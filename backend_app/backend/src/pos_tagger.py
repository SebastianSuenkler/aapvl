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

import sys
from nltk.tag.crf import CRFTagger
from nltk.tokenize import word_tokenize
import codecs

class POSTagger:
    """Simple POS-Tagger using conditional random fields.

    Attributes:
        tagger : the pretrained tagger

    """
    def __init__(self, model_file='models/health_claim_model.crf.tagger'):
        """Initialize tagger.

        Keyword Arguments:
            model_file : path to pretrained model 
                (default: ./models/health_claim_model.crf.tagger')

        """
        self.tagger = CRFTagger()
        self.tagger.set_model_file(model_file)

    def pos_tag_io(self):
        """Read from stdin, tag and write to stdout."""
        UTF8Reader = codecs.getreader('utf8')
        input_stream = UTF8Reader(sys.stdin)
        UTF8Writer = codecs.getwriter('utf8')
        output_stream = UTF8Writer(sys.stdout)

        for line in input_stream:
            for w in self.tagger.tag(word_tokenize(line.strip())):
                output_stream.write(w[0])
                output_stream.write("\t")
                output_stream.write(w[1])
                output_stream.write("\n")
            output_stream.write("\n")


    def pos_tag_lst(self, lst, output):
        """Tag every word of every sentence in lst and write to output.

        Arguments:
            lst : list of sentences
            output : filehandle
        """
        # assumption: every element in the list is a new sentence
        for e in lst:
            for w in self.tagger.tag(word_tokenize(e.strip())):
                output.write(w[0])
                output.write("\t")
                output.write(w[1])
                output.write("\n")
            output.write("\n")
