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

import product_crf
import anr_matcher
import sys
import preprocess

class InformationExtractor:
    """Functionality to extract information from product websites.

    Attributes:
        anr : extractor for "Artikelnummern"
        pr_name : crf to extract product names from webpage titles

    """
    def __init__(self):
        """Initialize all extractors."""
        self.anr = anr_matcher.ANRMatcher()
        self.pr_name = product_crf.ProductnameCRF()

    def extract_productname(self, title):
        """Extract productnames from webpage titles.

        Arguments:
            title : title from a webpage

        """
        tokens = product_crf.tokenize(title)
        feats = self.pr_name.seq2feat(tokens)
        labels = self.pr_name.predict([feats])
        ans = filter(lambda x: x[0] == 'AN', zip(labels[0], tokens))
        ans = map(lambda x: x[1], ans)
        return u' '.join(ans)

    def extract_artikelnummer(self, text):
        """Extract "Artikelnummern" from text.

        Arguments:
            text : some text

        """
        return self.anr.match(text)
