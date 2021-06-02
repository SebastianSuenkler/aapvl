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

import sklearn_crfsuite
from sklearn_crfsuite import metrics
from sklearn.model_selection import KFold
import numpy as np
import sys
import codecs
import os
import cPickle
import re

# TODO: maybe the tokenizer should be in preprocess!
token_pattern = re.compile(ur"[,.;:'\"*&#]|\b\w+\b", re.U)

def tokenize(t):
    return token_pattern.findall(t)
        
class ProductnameCRF:
    """A sequence labeling algorithm for product names using CRFs.

    Attributes:
        filename : name for the file that is used to store the classifier
        crf : conditional random field to label sequences

    """
    def __init__(self, new=False, x_file=None, y_file=None, cros_val=False):
        """Initialize the conditional random field.

        If new is False, it tries to read the classifier from
        :attr:`self.filename`. If this file does not exist or new is True, a
        new classifier is trained with the files x_file and y_file.

        If new is True and x_file or y_file is None, the function ends
        with an error.

        Keyword Arguments:
            new : if True, a new classifier is trained (default:false)
            x_file : file that contains the training sequences. Each
                sequence has to be in a single line. (default: None)
            y_file : file that contains the label sequences. The label
                sequences have to be in the same line as the corresponding
                training sequence in x_file. (default: None)
            cros_val : default: False

        """
        if not cros_val:
            self.filename = 'models/productname_crf.pkl'
            if os.path.isfile(self.filename) and not new:
                with open(self.filename, 'rb') as f:
                    self.crf = cPickle.load(f)
            else:
                if not x_file is None and not y_file is None:
                    if os.path.isfile(x_file) and os.path.isfile(y_file):
                        data, targets = self.load_files(x_file, y_file)
                    else:
                        # TODO: error
                        pass
                    self.train(data, targets)
        else:
            self.filename = 'models/product_crf_cros_val.pkl'

    def load_files(self, x_file, y_file):
        """Load training data from files.

        Arguments:
            x_file : file that contains the training sequences. Each
                sequence has to be in a single line.
            y_file : file that contains the label sequences. The label
                sequences have to be in the same line as the corresponding
                training sequence in x_file.

        """
        with codecs.open(x_file, 'r', encoding="utf-8") as f:
            x_seqs = f.readlines()
        with codecs.open(y_file, 'r', encoding="utf-8") as f:
            y_seqs = f.readlines()
        x_seqs = map(lambda x: tokenize(x), x_seqs)
        y_seqs = map(lambda x: x.split(), y_seqs)
        return np.array(x_seqs), np.array(y_seqs)
    
    def create_features(self, word_list, pos, l, title = set()):
        """Create a feature dictionary for a given word.

        The feature dictionary is build for the word at position pos
        in word_list.

        Arguments:
            word_list : list of words, for which a sequence of feature
                dictionaries should be created.
            pos : position of the current word in word_list
            l : length of word_list

        Keyword Arguments:
            title : a set with all common tokens in titles of websites
                (default: set())

        """
        w = word_list[pos]
        feat_dict = {"w": w,
                     "wlen": len(w),
                     "wposb": pos,
                     "wpose": l - pos + 1,
                     "wup": w.isupper(),
                     "wlow": w.islower(),
                     "wcap": w.istitle(),
                     "wnum": w.isdigit(),
                     "walnum": w.isalnum(),
                     "wal": w.isalpha(),
                     "wand": w == '&',
                     "wtitle": w in title
        }
        if pos > 0:
            b1 = word_list[pos - 1]
            feat_dict.update({
                "b1": b1,
                "b1len": len(b1),
                "b1posb": pos - 1,
                "b1pose": l - pos,
                "b1up": b1.isupper(),
                "b1low": b1.islower(),
                "b1cap": b1.istitle(),
                "b1num": b1.isdigit(),
                "b1alnum": b1.isalnum(),
                "b1al": b1.isalpha(),
                "b1and": b1 == '&',
                "b1star": b1 == '*',
                "b1q": b1 == '"' or b1 == "'",
                "b1title": b1 in title,
                "BOS": False
            })
        else:
            feat_dict["BOS"] = True
        if pos < l - 1:
            e1 = word_list[pos + 1]
            feat_dict.update({
                "e1": e1,
                "e1len": len(e1),
                "e1posb": pos - 1,
                "e1pose": l - pos,
                "e1up": e1.isupper(),
                "e1low": e1.islower(),
                "e1cap": e1.istitle(),
                "e1num": e1.isdigit(),
                "e1alnum": e1.isalnum(),
                "e1al": e1.isalpha(),
                "e1and": e1 == '&',
                "e1star": e1 == '*',
                "e1q": e1 == '"' or e1 == "'",
                "e1c": e1 == ',',
                "e1d": e1 == '.',
                "e1s": e1 == ';',
                "e1co": e1 == ':',
                "e1title": e1 in title,
                "EOS": False
            })
        else:
            feat_dict["EOS"] = True

        return feat_dict


    def seq2feat(self, seq):
        """Create a list with feature dictionaries for seq.

        A feature dictionary for every word in seq is created with the
        method :meth:`create_features`.

        Arguments:
            seq : list with words, for which a sequence of feature
                dictionaries should be build

        """
        return [self.create_features(seq, i, len(seq)) for i in range(len(seq))]

    def train(self, x_list, y_list):
        """Train a conditional random field.

        The trained conditional random field is stored in
        :attr:`self.filename`.

        Arguments:
            x_list : list with all training sequences
            y_list : list with all training labels. The labels have to be
                at the same index as the corresponding sequences.

        """
        # create feature dictionary for every word
        x_train = [self.seq2feat(seq) for seq in x_list]
    
        # initialize crfsuite-wrapper
        crf = sklearn_crfsuite.CRF(
            algorithm='lbfgs',
            c1=0.1,
            c2=0.1,
            max_iterations=100,
            all_possible_transitions=True
        )

        # fit the crf-object
        crf.fit(x_train, y_list)

        self.crf = crf
        with open(self.filename, 'wb') as f:
            cPickle.dump(crf, f)

    def test(self, x_test, y_test):
        """Test the conditional random field.

        The results are printed to stdout.

        Arguments:
            x_test : list with test sequences
            y_test : list with test labels. The labels have to be at the
                same index as the corresponding sequences.

        """
        x_feat = [self.seq2feat(seq) for seq in x_test]

        prediction = self.crf.predict(x_feat)

        sorted_labels = sorted(self.crf.classes_, key = lambda x: (x[1:], x[0]))

        print metrics.flat_classification_report(y_test, prediction, labels = sorted_labels, digits = 3)

    def predict(self, samples):
        """Predict the labels for samples.

        Arguments:
            samples : list of sequences, for which the labels shall be
                predicted

        """
        return self.crf.predict(samples)

# TODO: use this to create an option in backend to train and test the
# product crf

# this is for debugging don't check it in
def main():
    x_file = sys.argv[1]
    y_file = sys.argv[2]

    c = ProductnameCRF(cros_val=False, x_file=x_file, y_file=y_file)
    # data, targets = c.load_files(x_file, y_file)
    # kf = KFold(n_splits=5)
    # iteration = 0
    # for train, test in kf.split(data):
    #     sys.stdout.write("iteration: {0}\n".format(iteration))
    #     train_data, test_data = data[train], data[test]
    #     train_targets, test_targets = targets[train], targets[test]
    #     sys.stdout.write("training")
    #     c.train(train_data, train_targets)
    #     sys.stdout.write("testing")
    #     c.test(test_data, test_targets)

if __name__ == "__main__":
    main()
