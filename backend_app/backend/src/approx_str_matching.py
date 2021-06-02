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

from collections import defaultdict

class Matcher:
    """This class implements a fast text searching algorithm allowing for
    errors. This Algorithm is implemented after [0]. For every Pattern
    that should be searched in a text, there has to be a little
    preprocessing. So the Matcher Objects are for one pattern
    each. The Matcher needs the pattern upon its initialization, so
    the pattern has to be passed to the constructor.

    Attributes:
        s : default dictionary, that contains a bitvector for each
            character in the pattern. The bitvecor has the length of the
            pattern and contains a 1 at every position the corresponding
            character is in the pattern.
        m : length of the pattern.

    Note:
        [0]: Sun Wu and Udi Manber. 1992. Fast text searching: allowing
            errors. Commun. ACM 35, 10 (October 1992), 83-91.

    """
    def __init__(self, pattern):
        """Initialize the Matcher Object for a given pattern.

        Constructs the default dictionary :attr:`self.s` and inserts the
        bitvectors for every character in pattern.

        Arguments:
            pattern : pattern, that should be searched in the text.

        """
        # init the S-bitvectors for every character in pattern
        self.s = defaultdict(int)
        self.m = len(pattern)
        for idx, c in enumerate(pattern):
            self.s[c] |= 1 << (self.m - idx - 1)

    def match_exact(self, text):
        """Search the corresponding pattern in text.

        Returns a list with the ending positions in text of the
        matches.

        Arguments:
            text : the text to be searched.

        """
        results = []
        r = 0
        for idx, c in enumerate(text):
            r = ((r >> 1) | (1 << (self.m-1))) & self.s[c]
            if (r & 1):
                results.append(tuple([idx - self.m + 1, idx]))
        return results

    def match_approx(self, text, k):
        """Search the corresponding pattern in text with max k errors.

        Only the match with the lowest error rate for one position of
        the text is reported. Returns a list with the ending positions
        in text of the matches.

        Arguments:
            text : the text to be searched.
            k : number of allowed errors

        """
        if k == 0:
            return self.match_exact(text)
        results = []
        rs = [0]
        for d in range(1, k+1):
            rs.append(rs[-1] | (1 << (self.m-d)))
        rs_new = [0] * (k+1)
        stored = False
        for i, c in enumerate(text):
            for idx, r in enumerate(rs):
                if idx == 0:
                    rs_new[idx] = ((rs[idx] >> 1) | (1 << (self.m-1))) & self.s[c]
                    if rs_new[idx] & 1 and not stored:
                        results.append([idx, (i - self.m + 1, i)])
                        stored = True
                else:
                    rs_new[idx] = ((((rs[idx] >> 1) | (1 << (self.m-1)))
                                   & self.s[c]) |
                                   (((rs[idx-1] | rs_new[idx-1]) >> 1) |
                                    (1 << (self.m-1))) |
                                   rs[idx-1])
                    if rs_new[idx] & 1 and not stored:
                        results.append([idx, (i - self.m + 1, i)])
                        stored = True
            rs = rs_new
            stored = False

        results = self._filter_matches(results)
        results.reverse()
        return results

    # filters matches, so that only the match with the lowest error
    # rate for one part of the text is reported
    def _filter_matches(self, matches):
        print matches
        keep = True
        results = []
        if len(matches) == 0:
            return results
        last_elem = matches[-1]
        results.append(last_elem[1])
        for elem in reversed(matches[:-1]):
            if (last_elem[1][1] != elem[1][1] + 1 and
                last_elem[0] + 1 != elem[0]):
                results.append(elem[1])
            last_elem = elem
        return results

