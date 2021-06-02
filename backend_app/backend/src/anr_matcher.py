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

import re

class ANRMatcher:
    """A regular expression wrapper to match 'Artikelnummern'.

    Attributes:
        complete_re : regular expression that captures some common forms
            for 'Artikelnummern'

    """
    def __init__(self):
        """Initialize the regular expression wrapper."""
        word_lst = ['Artikelnummer', 'Bestellnummer', 'Artikel-Nr.',
                    'Art.-Nr.', 'ARTIKEL-NR.', 'Art. Nr.', 'Art.Nr.',
                    'ArtNr.', 'Artikel', 'Artikel-Code', 'Artikel-ID',
                    'ArtikelNr.', 'Artikelnr.#', 'Best.Nr.',
                    'Bestell-Nr.', 'Bestell-Nr', 'Bestell-Nummer',
                    'Bestellnr.', 'Nr.', 'Produkt-ID', 'Produkt-Nr.']
        word_re = re.compile(ur'|'.join(word_lst))

        self.complete_re = re.compile(ur'(?:' + word_re.pattern + ur'):?\s*([\d\w\-\.]{1,19})', re.U | re.I)

    def match(self, t):
        """Match t with the regular expression.

        Arguments:
            t : the text, against which :attr:`self.complete_re` shall be
                matched.

        """
        return self.complete_re.findall(t)

