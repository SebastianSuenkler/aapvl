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

import cPickle
import re
import sklearn_crfsuite
import codecs

class IngredientExtractor:
    """Statistical Word Model to extract ingredients lists correctly.

    A conditional random field is used to determine the correct
    borders depending on the probabilities given by the statistical
    word model.

    Attributes:
        vocabulary : dictionary, mapping all known ingredients to their
            frequence
        trigram_model : dictionary, mapping word-triples to their number
            of occurence at the end of seen ingredients lists
        base_model : dictionary, mapping words from european parliament
            speeches to their frequence
        crf : conditional random field to determine the correct borders
        whitelist : a set with known and allowed ingredients
        blacklist : a set with known and prohibited ingredients
        zutaten_pat : regular expression to match the beginning of an
            ingredients list
        token_pattern_just_words : regular expression to tokenize all
            words with more than 2 characters and discard the rest

    """
    def __init__(self, config, filename='models/crf_vocab_stuff_europarl_complete_count.pkl'):
        """Initialize the models.

        Arguments:
            config : dictionary with important configuration information

        Keyword Arguments:
            filename : path to a pickled file containing the trained
                statistical word model and the conditional random field
                (default: models/crf_vocab_stuff_europarl_complete_count.pkl)

        """
        try:
            whitelist_name = config["ingredients_whitelist"]
            blacklist_name = config["ingredients_blacklist"]
        except KeyError:
            sys.stderr.write("the config is incomplete! can't initialize IngredientExtractor without <ingredients_whitelist> and <ingredients_blacklist>. Please add a file with this key to the config.")
            sys.exit(1)
        with open(filename) as f:
            [vocabulary, trigram_model, base_model, crf] = cPickle.load(f)
        with codecs.open(whitelist_name, encoding="utf-8") as f:
            self.whitelist = set(map(lambda x: x.lower(), f.readlines()))
        with codecs.open(blacklist_name, encoding="utf-8") as f:
            self.blacklist = set(map(lambda x: x.lower(), f.readlines()))
        self.vocabulary = vocabulary
        self.trigram_model = trigram_model
        self.base_model = base_model
        self.crf = crf

        self.zutaten_pat = re.compile(ur'(\b|\s)Zutaten[\w]*:?', re.I | re.U)

        self.token_pattern_just_words = re.compile(ur"""\b\w\w+\b""", re.I | re.U)

    def tokenize_words(self, t):
        """Tokenize a text.

        Arguments:
            t : text to tokenize

        """
        return self.token_pattern_just_words.findall(t.lower())
        
    def extract_zutaten(self, text):
        """Extract tokens from all possible ingredients list.

        Arguments:
            text : text to extract from

        """
        res = []
        for p in self.zutaten_pat.finditer(text):
            end = 2000
            while True:
                tmp = text[p.start():p.start() + end]
                tokens = self.tokenize_words(tmp)
                if len(tokens) > 160:
                    results = tokens[:160]
                    break
                elif len(text) < (p.start() + end):
                    results = tokens
                    break
                else:
                    end += 500
            res.append(results)
        return res

    def prob_lm(self, w):
        """Calculate probability for a word to be in an ingredients list.

        Arguments:
            w : word to evaluate

        """
        try:
            p_lm = self.vocabulary[w]
        except KeyError:
            p_lm = 0
        return p_lm

    def prob_nlm(self, w):
        """Calculate probability for a word not to be in an ingredients list.

        Arguments:
            w : word to evaluate

        """
        try:
            p_nlm = self.base_model[w]
        except KeyError:
            p_nlm = 0
        return p_nlm

    def prob_end(self, w2, w1, w):
        """Calculate probability for words to be at the end.

        To obtain a better estimate of the probability for a word,
        this function should be used three times for each word. The
        following setups should be used to evaluate the last word,
        given a list of tokens l: prob_end(l[-3], l[-2], l[-1]),
        prob_end(l[-2], l[-1], None) and prob_end(l[-1], None, None).

        Arguments:
            w2 : word preceding the word to evaluate by 2
            w1 : word preceding the word to evaluate by 1
            w : word to evaluate

        """

        try:
            p_end = self.trigram_model[(w2, w1)][w]
        except KeyError:
            p_end = 0
        return p_end

    def create_feat_lists(self, tokens):
        """Create lists with probabilities for each token.

        For each token the probability to be in an ingredient list,
        not to be in an ingredient list and to be at the end of an
        ingredient list is calculated. For convenience, three lists
        are returned containing all the probabilities to be in an
        ingredient list, not to be in an ingredient list and to be at
        the end of an ingredient list.

        Arguments:
            tokens : a list of tokens to evaluate

        """
        p_lm_lst = map(lambda x: self.prob_lm(x), tokens)
        p_nlm_lst = map(lambda x: self.prob_nlm(x), tokens)
        p_end_lst = []
        p_1_lst = []
        p_2_lst = []
        p_3_lst = []
        for idx, t in enumerate(tokens):
            p_1 = self.prob_end(tokens[idx-2], tokens[idx-1], t) if idx > 1 else 0
            p_2 = self.prob_end(tokens[idx-1], t, None) if idx > 0 else 0
            p_3 = self.prob_end(t, None, None)
            if t == u'anbau':
                print p_1, p_2, p_3
            p_1_lst.append(p_1)
            p_2_lst.append(p_2)
            p_3_lst.append(p_3)
        return p_lm_lst, p_nlm_lst, p_1_lst, p_2_lst, p_3_lst

    def create_vec_list(self, tokens):
        """Create a list of vectors for the crf for tokens.

        Arguments:
            tokens : a list of tokens to evaluate

        """
        p_lm_lst, p_nlm_lst, p_1_lst, p_2_lst, p_3_lst = self.create_feat_lists(tokens)
        feats = [{'p':p, 'n':n, 'p1':p1, 'p2':p2, 'p3':p3} for p, n, p1, p2, p3 in zip(p_lm_lst, p_nlm_lst, p_1_lst, p_2_lst, p_3_lst)]
        return feats
        
    def extract(self, text):
        """Extract the ingredient list and calculate an occurence value.

        The occurence value is an estimate for how often an ingredient
        list with the same ending has been seen previously.

        Arguments:
            text : text to extract the ingredient list from

        """
        results = []
        extracted = self.extract_zutaten(text)
        for tokens in extracted:
            vec = self.create_vec_list(tokens)
            prediction = self.crf.predict([vec])[0]
            if 'LM' in prediction:
                result = dict()
                first = prediction.index('LM')
                last = len(prediction) - prediction[::-1].index('LM')
                result['ingredients'] = tokens[first:last]
                result['count'] = max(vec[last-1]['p1'], vec[last-1]['p2'], vec[last-1]['p3'])
                results.append(result)
        return results

    def whitelist_check(self, lst):
        """Check if an ingredient from lst is not known.

        Arguments:
            lst : ingredient list
        """
        fishy = []
        for l in lst:
            if l not in self.whitelist:
                fishy.append(l)
        return fishy

    def blacklist_check(self, lst):
        """Check if an ingredient from lst is prohibited.

        Arguments:
            lst : ingredient list

        """
        forbidden = []
        for l in lst:
            if l in self.blacklist:
                forbidden.append(l)
        return forbidden
                
