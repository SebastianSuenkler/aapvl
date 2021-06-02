# MIT License
#
# Copyright (c) 2016 - 2018 Hamburg University of Applied Sciences - HAW Hamburg
#
# Authors: Dorle Osterode
#
# Permission is hereby granted, free of charge, to any person obtaining a copy
# of this software and associated documentation files (the "Software"), to deal
# in the Software without restriction, including without limitation the rights
# to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
# copies of the Software, and to permit persons to whom the Software is
# furnished to do so, subject to the following conditions:
#
# The above copyright notice and this permission notice shall be included in all
# copies or substantial portions of the Software.
#
# THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
# IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
# FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
# AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
# LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
# OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
# SOFTWARE.

from sklearn.datasets import load_files
from sklearn.model_selection import KFold
from sklearn.feature_extraction.text import TfidfVectorizer
from sklearn.pipeline import Pipeline
from sklearn.svm import LinearSVC
import numpy as np
from scipy.optimize import minimize
import sys
import os
import math
import logging
from sklearn.feature_selection import SelectFromModel
from sklearn.ensemble import RandomForestClassifier
import codecs
from nltk.corpus import stopwords

class Probability:
    """A Probability-Calculator for the output of a SVM. 

    This implementation follows the procedure in [1] with additional
    changes from [2].

    Attributes:
        A, B : the calculated weights for the probability function:
            P(class | input) = 1 / ( 1 + exp(A * input + B))

    Note:
        References:
            [1] John C. Platt, Probabilistic Outputs for Support Vector
                Machines and Comparison to Regularized Likelihood Methods, 2000
            [2] Lin et al, A note on Platt's probabilistic outputs for support
                vector machines, 2007

    """

    def __init__(self, type_, pipe = None, directory = None, new = False):
        """Initialize the Probability-Calculator.

        Tries to read already trained parameters A and B from the file
        "models/type__sigmoid.txt" and expects them to be in the following
        format:
        ::
          A <number>
          B <number>

        if new is True or the file doesn't exist, and pipe or
        directory is None this function ends with an error.

        Arguments:
            type_ : string that indicates the type of the
                classifier. this string is used to identify the trained
                sigmoidal function in the future

        Keyword Arguments:
            pipe : pipeline to use to generate the training set (default:
                None)
            directory : path to the directory of training samples
                (default: None)
            new : if True, new parameters will be learned with the
                training samples in directory (default: False)

        """
        self._ts = []
        self._fs = []
        self.A = 0
        self.B = 0
        train_file = "models/{0}_sigmoid.txt".format(type_)

        logging.debug("initializing probability object")
        if (os.path.isfile(train_file) and not new):
            logging.debug("loading the variables")
            self.read_from_file(train_file)
            logging.debug("initialized probability object")
        elif not directory is None and not pipe is None:
            logging.debug("training and storing the variables")
            self.train_probs_orig(pipe, directory)
            self.store_to_file(train_file)
            logging.debug("initialized probability object")
        else:
            logging.warn("can't initialize a probability object. performing calculactions with A = 0 and B = 0.")

    def read_from_file(self, _file_):
        """ Read parameters A and B from _file_.

        Arguments:
            _file_ : filename of file to read from.

        """
        with open(_file_) as f:
            for line in f:
                if line.startswith('A'):
                    _, A = line.split()
                    self.A = float(A)
                elif line.startswith('B'):
                    _, B = line.split()
                    self.B = float(B)
                else:
                    logging.warn("detected unknown line in %s: %s", _file_, line)
                    
    def store_to_file(self, _file_):
        """ Store parameters A and B to _file_.

        Arguments:
            _file_ : filename of file to store to.
        """
        with open(_file_, 'w') as f:
            f.write("A {0}\n".format(self.A))
            f.write("B {0}\n".format(self.B))

    # objective function to optimize
    def _obj_fun(self, x):
        """Objective function for optimisation.

        For internal use only!

        Arguments:
            x : list or array that contains A and B (e.g. [A, B]).

        """
        sum_ = 0
        for t, f in zip(self._ts, self._fs):
            fab = x[0] * f + x[1]     # this is needed to avoid catastrophic cancellation
            if fab >= 0:              # see [2]
                sum_ += t * fab + np.log(1 + np.exp(-fab))
            else:
                sum_ += (t - 1) * fab + np.log(1 + np.exp(fab))
        return sum_

    # jacobi matrix/ gradient of the objective function according to
    # [2]
    def _jacobi(self, x):
        """Calculate second derivative of objective function.

        For internal use only!

        Arguments:
            x : list or array that contains A and B (e.g. [A, B])

        """
        x = np.asarray(x)
        sum_a = 0.0
        sum_b = 0.0
        for t, f in zip(self._ts, self._fs):
            fab = x[0] * f + x[1]
            if fab >= 0:
                p = (np.exp(-fab) / (1 + np.exp(-fab)))
            else:
                p = (1 / (1 + np.exp(fab)))
            sum_a += f*(t - p)
            sum_b += (t - p)
        der = np.zeros_like(x)
        der[0] = sum_a
        der[1] = sum_b
        return der

    def train_probs(self, directory):
        """Train the probability calculator.

        Generates a training set from the samples in directory and
        performs a minimization to learn the parameters A and B. The
        new parameters are not stored in a file. Please use
        :meth:`store_to_file` for this purpose. If the minimization is not
        successfull, it is reported on stderr.

        Arguments:
            directory : path to the directory with the initial training
                samples

        """
        # get ys and create fs with 3fold-cross validation and the classifier
        print "generating trainingset"
        fs, ys = self.generate_trainingset(directory)
        # transform ys to ts
        n_yes = np.sum(ys == 0) # TODO: this doesn't work
        n_no = np.sum(ys == 1) # TODO: this doesn't work
        t_hi = ((n_yes + 1) / (n_yes + 2))
        t_lo = (1 / (n_no + 2))
        ts = map(lambda x: t_hi if x == 0 else t_lo, ys)
        self._fs = fs
        self._ts = ts
        # initial guess
        A = 0
        B = np.log((n_yes + 1) / (n_no + 1))
        x0 = [A, B]
        print "can start to minimize"
        # get solution for the opimization problem
        res = minimize(self._obj_fun, x0, method = 'Newton-CG', jac = self._jacobi)
        if not res.success:
            sys.stderr.write("Couldn't find a good solution for this dataset\n")
        else:
            self.A = res.x[0]
            self.B = res.x[1]

    # the original algorithm is maybe better?
    def train_probs_orig(self, pipe, directory):
        """Train the probability calculator.

        Generates a training set from the samples in directory and
        performs the original algorithm from [1] to learn the
        parameters A and B. The new parameters are not stored in a
        file. Please use :meth:`store_to_file` for this purpose. If the
        minimization is not successfull, it is reported on stderr.

        Arguments:
            directory : path to the directory with the initial training
                samples

        """

        logging.debug("training with original algorithm")
        fs, ys = self.generate_trainingset(pipe, directory)
        # transform ys to ts
        n_yes = 0.0
        n_no = 0.0
        for y in ys:
            if y == 0:
                n_yes += 1
            else:
                n_no += 1

        max_iter = 100
        min_step = math.pow(10, -10)
        sigma = math.pow(10, -12)
        eps = math.pow(10, -5)
        t_hi = ((n_yes + 1.0) / (n_yes + 2.0))
        t_lo = (1.0 / (n_no + 2.0))
        ts = map(lambda x: t_hi if x == 0 else t_lo, ys)
        # initial guess and fval
        A = 0.0
        B = np.log((n_no + 1.0) / (n_yes + 1.0))
        fval = 0.0
        
        for t, f in zip(ts, fs):
            fab = A*f + B
            if fab >= 0:
                fval += t*fab + np.log(1.0 + np.exp(-fab))
            else:
                fval += (t - 1.0)*fab + np.log(1.0 + np.exp(fab))

        logging.debug("starting to minimize")
        it = 0
        while it < max_iter:
            it += 1
            # update gradient: H' = H + sigma I
            h11 = sigma
            h22 = sigma
            h21, g1, g2 = 0.0, 0.0, 0.0
            for t, f in zip(ts, fs):
                fab = A*f + B
                if fab >= 0:
                    p = np.exp(-fab)/(1.0 + np.exp(-fab))
                    q = 1.0/(1.0 + np.exp(-fab))
                else:
                    p = 1.0/(1.0 + np.exp(fab))
                    q = np.exp(fab)/(1.0 + np.exp(fab))
                d2 = p*q
                h11 += f*f*d2
                h22 += d2
                h21 += f*d2
                d1 = t - p
                g1 += f*d1
                g2 += d1
            # stopping criterion
            if abs(g1) < eps and abs(g2) < eps:
                break
            # finding Newton direction -inv(H') * g
            det = h11*h22 - h21*h21
            dA = -(h22*g1 - h21*g2)/det
            dB = -(-h21*g1 + h11*g2)/det
            gd = g1*dA + g2*dB

            # Line Search
            stepsize = 1.0
            while stepsize >= min_step:
                newA = A + stepsize * dA
                newB = B + stepsize * dB
                newf = 0.0 # new function value
                for t, f in zip(ts, fs):
                    fab = newA*f + newB
                    if fab >= 0:
                        newf += t*fab + np.log(1.0 + np.exp(-fab))
                    else:
                        newf += (t - 1.0)*fab + np.log(1.0 + np.exp(fab))
                if newf < (fval + 0.0001*stepsize*gd):
                    A = newA
                    B = newB
                    fval = newf
                    break
                else:
                    stepsize = stepsize / 2.0
            if stepsize < min_step:
                logging.warn("line search failed")
                break
        if it > max_iter:
            logging.warn("reaching max iter")
        print A, B
        self.A = A
        self.B = B

    def generate_trainingset(self, pipeline, directory):
        """Generate training data fs and ys.

        Generates a training set from the samples in directory
        consisting of the distance to the hyperplane for all points
        and their real labels (fs and ys). Therefore a 3fold
        cross-validation with a SVM is performed. The feature
        selection and parameters are not configurable.

        Arguments:
            pipeline : pipeline of classifier, which is used to generate
                the trainingset
            directory : path to the directory with the initial training samples

        """
        logging.debug("generating training and test sets")
        loaded_files = load_files(directory)
        kf = KFold(n_splits = 3)
        ys = []
        fs = []
        for train_index, test_index in kf.split(loaded_files.data):
            x_train = [ loaded_files.data[i] for i in train_index ]
            x_test = [ loaded_files.data[i] for i in test_index ]
            y_train = [ loaded_files.target[i] for i in train_index ]
            y_test = [ loaded_files.target[i] for i in test_index ]

            pipeline.fit(x_train, y_train)
            cur_fs = pipeline.decision_function(x_test)
            ys.extend(y_test)
            fs.extend(cur_fs)
        logging.debug("generated training and test sets")
        return fs, ys

    def calculate_probability(self, dec):
        """ Calculat the probability for dec to be in the positive class.

        Arguments:
            dec : distance to the hyperplane for this data point

        """
        fab = self.A * dec + self.B
        if fab >= 0:
            ret = (np.exp(-fab) / (1 + np.exp(-fab)))
        else:
            ret = (1 / (1 + np.exp(fab)))
        return round(ret, 2) * 100
