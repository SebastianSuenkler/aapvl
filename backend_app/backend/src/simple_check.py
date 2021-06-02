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

import approx_str_matching

class SimpleCheck:
    """Wrapper for approximative string matching.

    Attributes:
        matchers_lst : list of lists of different matchers that are
            used in every check method
        words_lst : list of lists of the corresponding search terms

    """
    def __init__(self):
        """Initialize the list of matchers and words as empty."""
        self.matchers_lst = []
        self.words_lst = []

    def add_list(self, lst):
        """Add a matcher for every word in lst.

        Adds a list of words that are searched for in the texts. The
        list should contain a word with multiple synomymous words.

        Arguments:
            lst : list of words that are added

        """
        matchers = map(lambda x: approx_str_matching.Matcher(x), lst)
        self.matchers_lst.append(matchers)
        self.words_lst.append(lst)

    def simple_check_text(self, text, k=0):
        """Check the text for occurences of terms.

        Arguments:
            text : text that should be searched

        Keyword Arguments:
            k : number of acceptable errors (default: 0)

        """
        results_lst = []
        results = []
        for i, matchers in enumerate(self.matchers_lst):
            for j, m in enumerate(matchers):
                r = m.match_approx(text, k)
                if len(r) > 0:
                    results.append(self.words_lst[i][j])
            results_lst.append(results)
            results = []
        if all([len(x) > 0 for x in results_lst]):
            # found at least one word for every list in text
            return results_lst
        else:
            return []


    def lst_distance(self, lst, distance):
        """Filter the matches within a given distance.

        Arguments:
            lst : list with matching results
            distance : allowed distance in characters

        """
        one = lst[0]
        every = True
        results = []
        for _, w in one:
            start = max(w[0] - distance, 0)
            end = w[1] + distance
            range_ = map(lambda y: filter(lambda x: start <= x[1][0] and x[1][1] <= end, y), lst)
            in_range = filter(lambda x: len(x) > 0, range_)
            if len(in_range) == len(lst):
                results.append(in_range)
        return results
        
    def range_check_text(self, text, distance, k=0):
        """Check for search terms within a given distance.

        Arguments:
            text : text to be searched
            distance : allowed distance of matches in characters

        Keyword Arguments:
            k : number of acceptable errors (default: 0)

        """
        results_lst = []
        results = []
        for i, matchers in enumerate(self.matchers_lst):
            for j, m in enumerate(matchers):
                r = m.match_approx(text, k)
                if len(r) > 0:
                    results.extend(map(lambda x: [self.words_lst[i][j], x], r))
            results_lst.append(results)
            results = []
        if all([len(x) > 0 for x in results_lst]):
            # found at least one word for every list in text. check if
            # there are words from every list, that have a match
            # within distance.
            tmp = self.lst_distance(results_lst, distance)
            if len(tmp) > 0:
                return tmp
            else:
                return []
        else:
            return []

def main():
    s = SimpleCheck()
    s.add_list(['foo', 'hallo', 'wurst'])
    s.add_list(['bar', 'ciao', 'kaese'])
    s.add_list(['baz', 'egal', 'vegan'])
    print s.simple_check_text("hier stehen nun foo und ganz zum schluss zu guter letzt bar und baz zusammen")
    print s.simple_check_text("schlechter witz: frage? wurst oder kaese: sagt der veganer: egal")
    print s.range_check_text("foo und bar und baz          wurst ciao egal", 20,0)

if __name__ == "__main__":
    main()
