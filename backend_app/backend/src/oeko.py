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
import codecs
from logos import find_logos

class Oeko:
    """Functionality to check 'Biohändler' for correct labelling.

    Attributes:
        buzz_reg : regular expression matching special buzzwords
        num_reg : regular expression matching german 'Ökonummern'
        legal_numbers : list of legal 'Ökonummern'

    """
    def __init__(self, config):
        """Initialize attributes.

        Arguments:
            config : dictionary with important configuration information.

        """
        self.buzz_reg = re.compile(ur'biologisch|ökologisch|bio|öko', re.I|re.U)
        self.num_reg = re.compile(ur'DE-ÖKO-\d{3}', re.I|re.U)
        try:
            oeko_numbers = config['legal_numbers']
        except KeyError:
            sys.stderr.write("the config is incomplete! can't initialize module without <legal_numbers>. Please add a file with this key to the config.")
            sys.exit(1)
        with codecs.open(oeko_numbers, encoding="utf-8") as f:
            self.legal_numbers = f.readlines()
        self.legal_numbers = map(lambda x: x.strip(), self.legal_numbers)

    def check_text(self, text):
        """Check text for buzzwords and 'Ökonummern'.

        The found 'Ökonummern' are verified if they are legal. Illegal
        'Ökonummern' are reported also.

        Arguments:
            text : text to check

        """
        buzz_words = self.buzz_reg.findall(text)
        numbers = self.num_reg.findall(text)
        legal = []
        illegal = []
        for n in numbers:
            if n in self.legal_numbers:
                legal.append(n)
            else:
                illegal.append(n)
        return {"ads":buzz_words, "legal":legal, "fake":illegal}

    def check_image(self, image_name):
        """Check a screenshot for the mandatory eu logo.

        Arguments:
            image_name : path to the screenshot

        """
        logos = find_logos.find_logos(image_name)
        return {"logos":logos}
