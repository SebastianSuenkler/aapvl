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
from sklearn.feature_extraction.text import TfidfVectorizer
from sklearn.pipeline import Pipeline
from sklearn.svm import LinearSVC
from sklearn.linear_model import SGDClassifier
import cPickle
import numpy as np
import sys
import os
import logging
from sklearn.feature_selection import SelectFromModel
from sklearn.ensemble import RandomForestClassifier
import codecs
from nltk.corpus import stopwords

import preprocess
import probability

class Classifier:
    """A classification pipeline for different settings.

    Classifier implements an abstract interface to the underlying SVM
    for shop, food and product websites classification. It performs
    feature selection, feature extraction and classification. The
    different parameters for all three classifier instatiations are
    choosen with cross-validation and are hard coded.

    Attributes:
        pipeline : pipeline of a TfidfVectorizer, a RandomForrest for
            feature selection and a linear SVM.
        type_ : a string that describes the instantiation. Valid 
            strings: 'shop', 'food' and 'product'
        filename : filename of the file where the pickled classifier
            is stored.
        prob : an instance of :mod:`Probability` that is trained on the same
            data.

    """
    def __init__(self, config, directory = None, new = False, type_ = 'shop'):
        """Initialize the pipeline.

        Constructor to initialize the pipeline. This function tries to load
        the classifier from the file :attr:`self.filename`. If this file does
        not exist or new is True, a new classifier is trained and stored.

        If the file :attr:`self.filename` does not exist or new is True, and
        directory is None the function ends with an error

        Arguments:
            config : dictionary with important configuration information

        Keyword arguments:
            directory : path to the directory with the training samples
              (default: None)
            new : if True, a new classifier is trained with the set from
              directory (default: False)
            type_ : specifies the type of the classifier. Valid strings:'shop',
              'food', 'product'. (default: 'shop')
        """
        logging.debug("initializing classifier")
        self.type_ = type_
        self.filename = 'models/test_{0}_classifier.pkl'.format(type_)
        if os.path.isfile(self.filename) and not new:
            logging.debug("loading classifier")
            with open(self.filename, 'rb') as f:
                self.pipeline = cPickle.load(f)
            self.prob = probability.Probability(type_)
        else:
            if not directory is None:
                logging.debug("training classifier")
                self.train(directory, config)
                self.prob = probability.Probability(type_, self.pipeline, directory, True)
            else:
                logging.error("can't train new classifier without a directory")
        logging.debug("initialized classifier")

    def _create_pipeline(self, type_, config):
        """Create a pipeline for training.

        The pipeline consists of feature extraction, feature
        selection and classification. The used parameters are hard
        coded and depend on the parameter type_.

        This function is for internal use only!
        
        Arguments:
            type_ : specifies the type of the classifier. Valid strings:
                'shop', 'food', 'product'.

        """
        if type_ == 'shop':
            stop = stopwords.words('german')
            pipeline = Pipeline([
                ('tfidf', TfidfVectorizer(stop_words = stop)),
                ('forest', SelectFromModel(estimator=RandomForestClassifier(n_estimators=100, random_state=0),
                                           threshold=0.0004)),
                ('clf', SGDClassifier(alpha=0.00001)),
            ])
        elif type_ == 'food':
            voc = dict()
            try:
                vocab_file = config['food_vocab']
            except KeyError:
                sys.stderr.write("the config is incomplete! can't train classifier without a <food_vocab>. Please add a file with this key to the config.")
                sys.exit(1)
            with codecs.open(vocab_file, encoding='utf-8') as f:
                sys.stdout.write("opened the vocab file!!!")
                sys.exit(1)
                count = 0
                for line in f:
                    try:
                        __ = voc[line.strip().lower()]
                    except KeyError:
                        voc[line.strip().lower()] = count
                        count += 1

            pipeline = Pipeline([
                ('tfidf', TfidfVectorizer(vocabulary=voc)),
                ('clf', SGDClassifier(alpha=0.0001)),
            ])

        elif type_ == 'product':
            pipeline = Pipeline([
                ('tfidf', TfidfVectorizer()),
                ('forest', SelectFromModel(estimator=RandomForestClassifier(n_estimators=100, random_state=0),
                                           threshold=0.0004)),
                ('clf', SGDClassifier(alpha=0.0000001)),
            ])
        return pipeline

    def load_files(self, container_path):
        """Load the files in container_path.

        A specific structure of directories is assumed within
        container_path. The names of the subdirectories are taken as
        target names and the data within the subdirectories is assumed
        to be within different classes. The assumed structure is:

        container_path/
            0/
                file1.html
                file2.html
                ...
            1/
                file3.html
                file4.html
                ...
        
        All the files within the subdirectories are preprocessed with
        BeautifulSoup. So HTML-files can directly be used as input for
        training and testing purposes. BeautifulSoup handles plain
        text files well, so that they can also be used for loading
        with this function. Even directories with mixed types of text
        files are possible.

        Arguments:
            container_path : path to the containing directory

        """
        p = preprocess.Preprocessor()
        target = []
        filenames = []

        folders = [f for f in sorted(os.listdir(container_path))
                   if os.path.isdir(os.path.join(container_path, f))]

        for label, folder in enumerate(folders):
            folder_path = os.path.join(container_path, folder)
            documents = [os.path.join(folder_path, d)
                         for d in sorted(os.listdir(folder_path))]
            target.extend(len(documents) * [label])
            filenames.extend(documents)

        data = []
        for filename in filenames:
            text, _ = p.preprocess_file(filename, links=False)
            data.append(text)
        return data, target

    def train(self, training_data, config):
        """Create and train a pipeline.

        For Training the data in training_data is used. After training
        the pipeline is stored in the file :attr:`self.filename`.

        Arguments:
            training_data : path to the directory with the training
                data. A special directory structure is assumed: the directory
                training_data should contain two sub-directories '0' and
                '1'. Sub-directory '0' contains the positive examples and
                sub-directory '1' contains the negative examples (e.g. when
                :attr:`self.type_` = 'shop', all shops are contained in '0' and all
                non-shops are contained in '1').
            config : dictionary with necessary configuration details.

        """
        logging.debug("training classifier")
        data, target = self.load_files(training_data)
        logging.debug("loaded {0} files".format(len(data)))
        pipeline = self._create_pipeline(self.type_, config)
        pipeline.fit(data, target)
        # store the pipeline to a specific file
        with open(self.filename, 'wb') as f:
            cPickle.dump(pipeline, f)
        self.pipeline = pipeline
        logging.debug("trained classifier")

    def train_batch_dir(self, directory):
        """Wrap :meth:`train_batch` to be used with directories.

        Arguments:
            directory : path to directory with data.

        """
        data, labels = self.load_files(directory)
        self.train_batch(data, labels)

    def train_batch(self, data, labels):
        """Refine :attr:`self.pipeline` with batch online learning.

        This function is for online-learning the classifier with
        e.g. manually labeled data. It only trains the classifier and
        not the feature extraction, feature selection and probability
        distribution part of the pipeline. For training data and
        labels are used as a batch. After training the classifier,
        the whole pipeline is stored in the file :attr:`self.filename`.

        Arguments:
            data : a list with the text from the manually labeled websites.
            labels : a list with the correspondin labels, that were
                assigned by a human.

        """
        logging.debug("updating classifier with batch training")
        for selector in self.pipeline.steps[:-1]:
            data = selector[1].transform(data)
        # classes argument needed for the first call to partial fit
        # and could be omitted. contains a list with all labels within
        # the complete data set.
        self.pipeline.named_steps['clf'].partial_fit(data,
                                                     labels,
                                                     classes=[0, 1])
        # store the pipeline to a specific file
        tmp = self.filename + '.new'
        with open(tmp, 'wb') as f:
            cPickle.dump(self.pipeline, f)
        # rename the new classifier to the old classifier
        os.rename(tmp, self.filename)
        logging.debug("updated classifier with batch training")

    def test(self, test_data, prob = False):
        """Test :attr:`self.pipeline`.

        Tests the trained pipeline with the data from test_data. After
        predicting the label, the true positives, false positives,
        false negatives, true negatives, precision, recall and
        accuracy are calculated and printed on stdout.

        Arguments:
            test_data : path to the directory with the test data. The
                same directory structure as for :meth:`train` is assumed.

        Keyword Arguments:
            prob : if True, :meth:`predict_prob` is used to get the
                classification instead of :meth:`predict`. (default: False)

        """
        data, target = self.load_files(test_data)
        predicted = self.pipeline.predict(data)
        if prob:
            predicted_pr = self.predict_prob(data)
            for pr, pr_prob in zip(predicted, predicted_pr):
                print "predicted label: {0}, prob: {1}".format(pr, pr_prob)

        target = np.array(target)
        TP = np.sum(np.logical_and(predicted == 0, target == 0))
        TN = np.sum(np.logical_and(predicted == 1, target == 1))
        FP = np.sum(np.logical_and(predicted == 0, target == 1))
        FN = np.sum(np.logical_and(predicted == 1, target == 0))

        accuracy = np.mean( predicted == target )
        precision = (float(TP) / float(TP + FP))
        recall = (float(TP) / float(TP + FN))

        sys.stdout.write("TP: {0}, FP: {1}, FN: {2}, TN: {3}\n".format(TP, FP, FN, TN))
        sys.stdout.write("precision: {0:.3f}, recall: {1:.3f}, accuracy: {2:.3f}\n".format(precision, recall, accuracy))

    def predict(self, data):
        """Predict classes for data.

        Predicts the class label for all data points in data.

        Arguments:
            data : list of data points to be classified.

        """
        ret = self.pipeline.predict(data)
        return ret

    def predict_prob(self, data):
       """Predict the membership probability for the positive class.

       Predicts the probability for all data points in data to be a
       member of the positive class.

       Arguments:
           data : a list of data points to be classified.

       """
       f = self.pipeline.decision_function(data)
       pr = []
       for i in f:
           pr.append(self.prob.calculate_probability(i))
       return pr
