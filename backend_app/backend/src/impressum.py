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
import re
import codecs
import logging
from collections import defaultdict

import impressum_crf

class Impressum_handler:
    """Extracts addresses from text.

    Holds a trained Conditional Random Field to identify addresses in
    text. The potential addresses are searched with a regular
    expression, that matches 5-digit numbers followed by text, and
    labeled with the CRF.

    Attributes:
        regex : a compiled regular expression to extract regions around a
            postal code
        imp : a trained CRF which is accessible through ImpressumCRF
        kr_mapping : a mapping from postal codes to regions

    """

    def __init__(self, config):
        """Initialize regex and load the trained CRF.

        Arguments:
            config : dictionary, that contains configuration. The entry
                with the key "map_file" is interpreted as a csv file
                containing a mapping from postal code to regions.

        """
        try:
            map_file = config["map_file"]
        except KeyError:
            sys.stderr.write("the config is incomplete! can't initialize Impressum_handler without <map_file>. Please add a file with this key to the config.")
            sys.exit(1)
        # initialize a regular expression to match plz
        self.regex = re.compile(ur'\s+([D|DE]-)?\d{5}\s+[a-zA-Z]+', re.I | re.U)
        # TODO: get the stuff for training here
        self.imp = impressum_crf.ImpressumCRF()
        self.kr_mapping = self._create_mapping(map_file)

    def _create_mapping(self, map_file):
        """Create mapping from postal codes to regions.

        This function is for internal use only!

        Arguments:
            map_file : file that contains a mapping from postal codes to
                regions. A special csv format is assumed.

        """
        with codecs.open(map_file, encoding='utf-8') as f:
            lst = f.readlines()
        map_ = dict()
        reg = re.compile(ur'((?:[^,"]|"[^"]*")+)', re.I | re.U)
        for line in lst:
            plz, bds, kr = reg.split(line.strip())[1::2]
            map_[plz] = [bds, kr]
        return map_

    def process_text(self, t):
        """Search and return all addresses in t.

        Arguments:
            t : the text to process.

        """
        # iterate over all the matches and store the first two streets
        all_adr = []
        for m in self.regex.finditer(t):
            start = 0 if m.start() < 100 else m.start() - 100
            end = len(t) if m.end() + 31 > len(t) else m.end() + 31
            p = t[start:end]
            logging.debug(u"{0}".format(p.replace('\n', ' ')))
            # create feature vector for this string
            p_lst = p.split()
            feat = self.imp.seq2feat(p_lst)
            # predict labels
            labels = self.imp.predict([feat])
            # extract and return the address segments
            parts = 1
            ot = False
            # FN=1, ST=2, NR=2, PLZ=4, CI=8, CO=16
            flag_map={"FN": 1, "ST":2, "NR":2, "PLZ":4, "CI":8, "CO":16}
            name_map={"FN":"Unternehmen", "ST":"Strasse", "NR":"Strasse",
                      "PLZ":"PLZ", "CI":"Ort", "CO":"Land",
                      1:"Unternehmen", 2:"Strasse",
                      4:"PLZ", 8:"Ort", 16:"Land"}
            flags = 0
            add = defaultdict(str)
            for i, st in enumerate(labels[0]):
                if st == "OT" and flags > 0:
                    ot = True
                elif st != "OT":
                    flags |= flag_map[st]
                    if ot:
                        parts += 1
                        ot = False
                    add[name_map[st]] += p_lst[i] + " "
            for flag in flag_map.itervalues():
                if not flags & flag:
                    add[name_map[flag]] = None
            if flags > 0:
                if flags & 4:
                    try:
                        bds, kr = self.kr_mapping[add['PLZ'].strip()]
                        add['Bundesland'] = bds
                        add['Kreis'] = kr
                    except KeyError:
                        logging.warn("couldn't find bds and kr for {0}".format(add[2]))
                        add['Bundesland'] = None
                        add['Kreis'] = None
                logging.debug(add)
                all_adr.append([add, flags, parts])
        return all_adr
