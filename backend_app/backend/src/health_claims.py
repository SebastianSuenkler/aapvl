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
import subprocess
import cPickle
import logging
import nltk.tokenize.punkt
from nltk.tokenize import word_tokenize
import os
import signal
import sys

import my_exceptions
import pos_tagger
import conll_parser
import simple_check

class HealthClaims:
    """Different strategies to check for not allowed health claims.

    The first strategy assumes a list with simple substances and a
    list with diseases (default: './health_claim_substances.txt'
    and './health_claim_diseases.txt'). The text of all websites
    registered for this module is searched for every line in these
    lists. When one or more diseases are found in the text, the found
    substances and diseases are returned, while just a substance is
    not enough for a suspicion.

    The second strategy searches for all health claims in a
    list. Therefore a list with prohibited health claims has to be
    provided (default: './rejected_claims.txt'). In this file every
    line is interpreted as a health claim and searched for.

    The third strategy extracts all the relations from the text. Only
    relations with a verb phrase contained in a provided file
    (default: './vps.txt') are returned.

    Attributes:
        pattern_matcher_sub : a simple pattern matcher that can search
            for substances
        pattern_matcher_dis : a simple pattern matcher that can search
            for disease
        pattern_matcher_fix : simple pattern matcher for fix health
            claims
        sub : list with relevant substances
        dis : list with relevant diseases
        verbs : set with relevant verbs
        pos : Part-of-Speech-Tagger
        punkt_name : filename of pretrained sentence tokenizer
        punkt : pretrained sentence tokenizer

    """
    def __init__(self, config):
        """Initialize all the matchers.

        Arguments:
            config : dictionary with important configuration information.

        """
        # substances
        try:
            filename_sub = config["health_claim_substances"]
            filename_dis = config["health_claim_diseases"]
            filename_rej = config["health_claim_rejected"]
            filename_dek = config["health_claim_declination"]
        except KeyError:
            sys.stderr.write("the config is incomplete! can't initialize module without <health_claim_substances>, <health_claim_diseases>, <health_claim_rejected> and <health_claim_declination>. Please add for each a file with this key to the config.")
            sys.exit(1)
        try:
            self.path = config["parzu"]
        except KeyError:
            sys.stderr.write("config is incomplete! need path to parzu executable with key 'parzu'\n")
            sys.exit(1)
        with codecs.open(filename_sub, encoding = "utf-8") as f:
            lst_sub = f.readlines()
        self.sub = map(lambda x: x.strip().lower(), lst_sub)
        self.pattern_matcher_sub = simple_check.SimpleCheck()
        self.pattern_matcher_sub.add_list(self.sub)
        # disease
        with codecs.open(filename_dis, encoding = "utf-8") as f:
            lst_dis = f.readlines()
        self.dis = map(lambda x: x.strip().lower(), lst_dis)
        self.pattern_matcher_dis = simple_check.SimpleCheck()
        self.pattern_matcher_dis.add_list(self.dis)
        # fix patterns
        with codecs.open(filename_rej, encoding="utf-8") as f:
            fix = f.readlines()
        fix = map(lambda x: x.strip().lower(), fix)
        self.pattern_matcher_fix = simple_check.SimpleCheck()
        self.pattern_matcher_fix.add_list(fix)
        # stuff for third strategy
        self.pos = pos_tagger.POSTagger()
        self.punkt_name = 'models/punkt_tokenizer.pkl'
        with open(self.punkt_name, 'rb') as f:
            self.punkt = cPickle.load(f)
        self.verbs = set()
        with codecs.open(filename_dek, encoding='utf-8') as f:
            for line in f:
                if line == '\n':
                    continue
                else:
                    self.verbs.add(frozenset(line.strip().split()))


    def check_disease_substances(self, text):
        """Execute the first strategy.

        Arguments:
            text : the text that should be searched through
        """
        subs = self.pattern_matcher_sub.simple_check_text(text.lower())
        dis = self.pattern_matcher_dis.simple_check_text(text.lower())
        if len(dis) > 0:
            return subs, dis
        else:
            return [], []

    def check_fix_patterns(self, text):
        """Execute the second strategy.

        Arguments:
            text : the text that should be searched through
        """
        claims = self.pattern_matcher_fix.simple_check_text(text.lower())
        return claims

    def chunks(self, l, n):
        """Yield successive n-sized chunks from l."""
        for i in range(0, len(l), n):
            yield l[i:i + n]

    def check_semantic_relations(self, text):
        """Execute the third strategy.

        Arguments:
           text : the text that shoulb be searched
        """
        results = None
        try:
            lst = self.punkt.tokenize(text)
            with codecs.open('tmp_pos_tag.txt', 'w', encoding="utf-8") as f:
                self.pos.pos_tag_lst(lst, f)
        except my_exceptions.TooLongException, exp:
            logging.error("health_claims tagging")
            sys.stderr.write("health claims semantic: {0}\n".format(exp))
            return results
        else:
            try:
                with codecs.open('tmp_pos_tag.txt', 'r', encoding="utf-8") as f:
                    args = [self.path, "--input", "tagged"]
                    p = subprocess.Popen(args, stdin = f, stdout = subprocess.PIPE, preexec_fn=os.setsid)
                    conll, _ = p.communicate()
            except my_exceptions.TooLongException, exp:
                os.killpg(p.pid, signal.SIGHUP)
                logging.error("health_claims parzu")
                sys.stderr.write("health claims semantic: {0}\n".format(exp))
                return results
            else:
                trees = conll_parser.parse_tree(conll)
                results = []
                for t in trees:
                    r = conll_parser.get_relation(t)
                    if frozenset(r['verb']) in self.verbs:
                        sup_count = 0
                        for np in r['np']:
                            for w in word_tokenize(np):
                                if w.lower() in self.dis or w.lower() in self.sub:
                                    sup_count += 1
                        results.append([r, sup_count])
                results.sort(key=lambda x: x[1])
                return results
