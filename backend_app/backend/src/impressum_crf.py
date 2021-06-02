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
import sys
import codecs
import os
import cPickle

# path to a file which contains city names.
city_file = '/home/dorle/Dokumente/data/osm_germany/cities.txt'

# possible delimiters
delim = [',', '.', ':', ';', '/', ',:', '.:', ',;', '.;']

# possible "geschäftsformen"
coop = {"ag",
        "aktiengesellschaft",
        "aör",
        "co",
        "co.",
        "co.kg",
        "commerce",
        "e.",
        "eigenbetrieb",
        "einzelunternehmen",
        "eg",
        "ev",
        "e.k.",
        "e.v.",
        "ewiv",
        "gag",
        "gbr",
        "german-reit",
        "gesellschaft",
        "ggmbh",
        "gmbh",
        "g-reit",
        "haftungsbeschränkt",
        "(haftungsbeschränkt)",
        "handelsgesellschaft",
        "invag",
        "kg",
        "kgaa",
        "kör",
        "limited",
        "ltd.",
        "mbb",
        "mbh",
        "ohg",
        "partenreederei",
        "partg",
        "plc",
        "regiebetrieb",
        "reit-ag",
        "se",
        "sce",
        "shop",
        "stiftung",
        "sup",
        "ug",
        "verwaltungs-gmbh",
        "vvag",
        "v.",
        "webshop",
        "&",
        }

# add every word in coop with every possible delimiter
tmp = set()
for d in delim:
    for e in coop:
        tmp.add(e + d)
coop.update(tmp)

# some common words that are used before the shop name
name_pre = {"firma"}

# some words that are used before the owner's name
not_name_pre = {"inhaber",
                "inhaberin",
                "inh",
                "herr",
                "frau",
                "geschäftsführender",
                "gesellschafter",
                "geschäftsführer",
                "rstv",
                "rundfunkstaatsvertrag",
                "präsident",
                "intendant"}

# add every word in not_name_pre with every possible delimiter
tmp = set()
for d in delim:
    for e in not_name_pre:
        tmp.add(e + d)
not_name_pre.update(tmp)

# "verantwortlich"
contact = {"adr",
           "adressat",
           "adresse",
           "anbieter",
           "anbieterinformationen",
           "anbieterkennung",
           "anbieterkennzeichnung",
           "anschrift",
           "betreiber",
           "diensteanbieter",
           "firma",
           "firmenadresse",
           "firmenanschrift",
           "firmendaten",
           "firmeninformationen",
           "firmeninformation",
           "firmensitz",
           "geschäftsadresse",
           "hauptsitz",
           "hausanschrift",
           "herausgeber",
           "homepage-betreiber",
           "inhalt",
           "impressum",
           "kontakt",
           "kontaktdaten",
           "kontaktadresse",
           "redaktionsanschrift",
           "sitz",
           "telemediengesetz",
           "tmg",
           "postanschrift",
           "zweigniederlassung"}

# add every word in contact with every possible delimiter
tmp = set()
for d in delim:
    for e in contact:
        tmp.add(e + d)
contact.update(tmp)

# possible abbreviations for "germany"
de_alternativen = {"deutschland", "de", "d", "brd",
                   "bundesrepublik deutschland", "bundesrep. deutschland",
                   "bundesrep deutschland", "germany", "ge"}

# some common first words in german street names
street_pre = {"an den", "an", "am", "achter", "achtern", "allee", "alt", "alte",
              "alter", "alten", "altem", "altes", "auf", "aufm", "äußere",
              "bei", "beim", "bi de", "bürgerm\.", "dr\.", "geschw.", "gewerbe",
              "graf", "groß", "große", "großer", "großes", "hinter", "hintere",
              "hinterer", "hinteres", "hinterm", "hohe", "hoher", "hohes",
              "hohen", "im", "in", "innere", "kleine", "kleiner", "kleines",
              "mittlere", "mittlerer", "mittleres", "nach", "neben", "neu",
              "neue", "neuer", "neues", "obere", "oberer", "oberes", "op",
              "opn", "platz", "platz des", "platz der", "prof.", "rue",
              "straße", "straße des", "st.", "über", "übere", "überer",
              "überes", "unter", "untere", "unterer", "unteres", "up", "vor",
              "weg", "zu", "zur", "zum"}

# some common last words in german street names
street_post = {"allee", "arcaden", "arkaden", "brücke", "boulevard",
               "chaussee", "chausee", "chause", "chausse", "gasse",
               "weg", "straße", "strasse", "str.", "st", "str",
               "passage", "platz", "ring", "tor", "nord", "süd",
               "west", "ost", "ufer", "damm", "anlage", "tor",
               "brunnen", "pfad", "postfach", "landstrasse",
               "landstraße", "landstr", "landstr.", "furt", "dorfstrasse",
               "dorfstraße", "dorfstr", "dorfstr."}

class ImpressumCRF:
    """A sequence labeling algorithm for german street names using CRFs.

    Attributes:
        cities : set with all german city names (from wikipedia)
        filename : name for the file that is used to store the classifier
        crf : conditional random field to label sequences

    """
    def __init__(self, cities=city_file, new=False, x_file=None, y_file=None):
        """Initialize the conditional random field.

        If new is False, it tries to read the classifier from
        :attr:`self.filename`. If this file does not exist or new is True, a
        new classifier is trained with the files x_file and y_file.

        If new is True and x_file or y_file is None, the function ends
        with an error.

        Keyword Arguments:
            cities : filename of a city-file (default: city_file)
            new : if True, a new classifier is trained (default: False)
            x_file : file that contains the training sequences. Each
                sequence has to be in a single line. (default: None)
            y_file : file that contains the label sequences. The label
                sequences have to be in the same line as the corresponding
                training sequence in x_file. (default: None)

        """
        self.filename = 'models/impressum_crf.pkl'
        if os.path.isfile(self.filename) and not new:
            with open(self.filename, 'rb') as f:
                l = cPickle.load(f)
                self.crf = l[0]
                self.cities = l[1]
        else:
            if not x_file is None and not y_file is None:
                with codecs.open(x_file, encoding='utf-8') as f:
                    x_list = f.readlines()
                with codecs.open(y_file, encoding='utf-8') as f:
                    y_list = f.readlines()
                x_list = map(lambda x: x.split(), x_list)
                y_list = map(lambda x: x.split(), y_list)
                with codecs.open(cities, encoding = "utf-8") as f:
                    city_list = f.readlines()
                self.cities = set(city_list)
                self.train(x_list, y_list)
                with open(self.filename, 'wb') as f:
                    cPickle.dump([self.crf, self.cities], f)

    def create_features(self, word_list, pos):
        """Create a feature vector for the word at pos in word_list.

        Arguments:
            word_list : list of words
            pos : position in word_list

        """
        word = word_list[pos]
        feature_dict = {
            "word":word.lower(),
            "pos":pos,
            ":":word[-1] == ':',
            "capitalized":word.istitle(),
            "upper":word.isupper(),
            "digit":all(ch.isdigit() for ch in word),
            "alpha":all(not ch.isdigit() for ch in word),
            "de":word.lower() in de_alternativen,
            "spre":word.lower() in street_pre,
            "spost":word.lower() in street_post,
            "npre":word.lower() in name_pre,
            "nnpre":word.lower() in not_name_pre,
            "coop":word.lower() in coop,
            "city":word in self.cities
        }
        if pos > 0:
            before = word_list[pos - 1]
            feature_dict.update({
                "before":before.lower(),
                "beforepos":pos-1,
                "before:":before[-1] == ':',
                "beforecapitalized":before.istitle(),
                "beforeupper":before.isupper(),
                "beforedigit":all(ch.isdigit() for ch in before),
                "beforealpha":all(not ch.isdigit() for ch in before),
                "beforede":before.lower() in de_alternativen,
                "beforepre":before.lower() in street_pre,
                "beforepost":before.lower() in street_post,
                "beforenpre":before.lower() in name_pre,
                "beforennpre":before.lower() in not_name_pre,
                "beforecoop":before.lower() in coop,
                "beforecity":before in self.cities,
                "BOS":False
            })
        else:
            feature_dict["BOS"] = True

        if pos < len(word_list) - 1:
            after = word_list[pos + 1]
            feature_dict.update({
                "after":after.lower(),
                "afterpos":pos+1,
                "after:":after[-1] == ':',
                "aftercapitalized":after.istitle(),
                "afterupper":after.isupper(),
                "afterdigit":all(ch.isdigit() for ch in after),
                "afteralpha":all(not ch.isdigit() for ch in after),
                "afterde":after.lower() in de_alternativen,
                "afterpre":after.lower() in street_pre,
                "afterpost":after.lower() in street_post,
                "afternpre":after.lower() in name_pre,
                "afternnpre":after.lower() in not_name_pre,
                "aftercoop":after.lower() in coop,
                "aftercity":after in self.cities,
                "EOS":False
            })
        else:
            feature_dict["EOS"] = True
    
        return feature_dict

    def seq2feat(self, seq):
        """Create a list with feature vectors for every word in seq.

        Arguments:
            seq : list of words

        """
        return [self.create_features(seq, i) for i in range(len(seq))]

    def train(self, x_list, y_list):
        """Train a conditional random field.

        For Training the CRF the samples from x_list and y_list are
        used. After training the CRF is stored to
        :attr:`self.filename`. :attr:`self.crf` is changed by this method.

        Arguments:
            x_list : list with the training sequences as list of words
            y_list : list with the label sequences as list of words. The
                label sequences have to correspond with the training sequences
                in x_list.

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

    def test(self, x_test, y_test):
        """Test the :attr:`self.crf`.

        For testing the test_data from x_test and y_test is used. This
        function prints a metric to stdout.

        Arguments:
            x_test : list with the test sequences as list of words
            y_test : list with the label sequences as list of words. The
                label sequences have to correspond with the test sequences in
                x_test.

        """
        x_feat = [self.seq2feat(seq) for seq in x_test]

        prediction = self.crf.predict(x_feat)

        sorted_labels = sorted(self.crf.classes_, key = lambda x: (x[1:], x[0]))

        print metrics.flat_classification_report(y_test, prediction, labels = sorted_labels, digits = 3)

    def predict(self, samples):
        """Predict the labels for every sample in samples.

        Arguments:
            samples : list of samples. The samples have to be lists of
                feature vectors.

        """
        return self.crf.predict(samples)

# this is for debugging don't check it in
# TODO: use this as template for a training flow from backend.py
def main():
    x_file = sys.argv[1]
    y_file = sys.argv[2]
    tx_file = sys.argv[3]
    ty_file = sys.argv[4]
    c = ImpressumCRF(new=True, x_file=x_file, y_file=y_file)
    with codecs.open(tx_file, encoding='utf-8') as f:
        x_list = f.readlines()
    with codecs.open(ty_file, encoding='utf-8') as f:
        y_list = f.readlines()
    x_list = map(lambda x: x.split(), x_list)
    y_list = map(lambda x: x.split(), y_list)
    c.test(x_list, y_list)

if __name__ == "__main__":
    main()
