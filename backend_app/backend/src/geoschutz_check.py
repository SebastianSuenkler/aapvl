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

import codecs
import re
from nltk import stem
from nltk.corpus import stopwords
import csv

# stuff to read and handle the csv format
def unicode_csv_reader(unicode_csv_data, dialect=csv.excel, **kwargs):
    # csv.py doesn't do Unicode; encode temporarily as UTF-8:
    csv_reader = csv.reader(utf_8_encoder(unicode_csv_data),
                            dialect=dialect, **kwargs)
    for row in csv_reader:
        # decode UTF-8 back to Unicode, cell by cell:
        yield [unicode(cell, 'utf-8') for cell in row]

def utf_8_encoder(unicode_csv_data):
    for line in unicode_csv_data:
        yield line.encode('utf-8')

class Geoschutz:
    """Functionality to detect EU-certificated product names.

    Attributes:
        s : dictionary with the keys 'PGI', 'PDO' and 'TSG'. For each key
            all the certificated productnames are stored in a set.
        stemmer : a german stemmer
        stop_words : a set with german stopwords
        max_n : the length of the longest productname contained in s
        compound_reg : regular expression with common delimiters for
            compound nouns

    """
    def __init__(self, config):
        """Initialize all attributes.

        Arguments:
            config : dictionary with important configuration information

        """
        try:
            filename = config["door_list"]
        except KeyError:
            sys.stderr.write("the config is incomplete! can't initialize geoschutz without a <door_list>. Please add a file with this key to the config.")
            sys.exit(1)
        # s contains all registered names as tuple
        self.s = {'PGI':set(), 'PDO':set(), 'TSG':set()}

        name_reg = re.compile(ur"[/;]")

        # stemmer to get the word stem
        self.stemmer = stem.snowball.GermanStemmer()
        
        # stop words that should be removed
        self.stop_words = set(stopwords.words('german'))

        self.max_n = len(max(self.s, key=len))

        self.compound_reg = re.compile(ur"[-\s]")
        
        with codecs.open(filename, encoding='utf-8') as f:
            reader = unicode_csv_reader(f)
            for row in reader:
                names = name_reg.split(row[1])
                for name in names:
                    try:
                        if row[3] == 'DE':
                            self.s[row[5]].add(frozenset(self.stem_all_words(
                                self.remove_stop_words(name.split()))))
                        else:
                            self.s[row[5]].add(frozenset(map(lambda x: x.lower(), name.split())))
                    except KeyError:
                        # skipping the first four lines
                        pass

    def remove_stop_words(self, lst):
        """Remove german stop words.

        Arguments:
            lst : list with words

        """
        return [e for e in lst if not e in self.stop_words]

    def stem_all_words(self, lst):
        """Stem all words.

        A german stemmer is used, so words from other languages may be
        stemmed incorrectly. The result can be compared to other
        stemmed words, because the procedure is deterministic.

        Arguments:
            lst : list with words

        """
        return [self.stemmer.stem(e) for e in lst]

    def create_ngrams(self, lst, min_n=1, max_n=1):
        """Create n-grams from a list.

        All n-grams for n in [min_n, max_n] are created.

        Arguments:
            lst : list of words

        Keyword Arguments:
            min_n : minimal length of an n-gram (default: 1)
            max_n : maximal length of an n-gram (default: 1)

        """
        original_tokens = lst
        tokens = []
        n_original_tokens = len(original_tokens)
        for n in xrange(min_n,
                        min(max_n + 1, n_original_tokens + 1)):
            for i in xrange(n_original_tokens - n + 1):
                tokens.append(frozenset(original_tokens[i: i + n]))
        return tokens

    def search(self, word):
        """Search a productname in all the certificated productnames.

        For the search word is normalized in the same ways, as all the
        productnames in :attr:`self.s`.

        Arguments:
            word : productname to search for in all certificated
                productnames.

        """
        comps = self.compound_reg.split(word)
        no_stop = self.remove_stop_words(comps)
        all_stem = self.stem_all_words(no_stop)
        ngrams = self.create_ngrams(all_stem, 1, self.max_n)

        set_product_name = set(ngrams)
        set_product_name.update(self.create_ngrams(map(lambda x: x.lower(), comps), 1, len(comps)))
        
        found = False
        for key, value in self.s.iteritems():
            if len(value.intersection(set_product_name)) > 0:
                found = True
                group = key
                break

        if found:
            return group
        else:
            return None
