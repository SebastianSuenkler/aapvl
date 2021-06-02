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

import urllib
from bs4 import BeautifulSoup
import re
from dateutil.parser import parse
import datetime
import sys
from postal.expand import expand_address
import cPickle
import os
import logging

class BiocStore:
    """Functionality to check certificates from an offline storage.

    The information about certificates is taken from the BioC
    database. Therefore all the certificates where downloaded and
    preprocessed in january 2018.

    Attributes:
        oeko_re : regular expression to extract the german 'Ökonummer'
        filename : path to a pickled bioc_store
        info_dict : dictionary mapping unique ids to certificate
            information
        mapping : dictionary mapping all normalised addresses to ids used
            in info_dict

    """
    def __init__(self):
        """Initialize the store."""
        self.oeko_re = re.compile(ur'DE-ÖKO-\d{3}', re.I|re.U)
        self.filename = 'models/bioc_store.pkl'
        # load the dictionaries info_dict and mapping
        with open(self.filename, 'rb') as f:
            self.info_dict, self.mapping = cPickle.load(f)

    def extract_all(self, filename):
        """Extract all certificate information from a website.

        For a certificate the following information is extracted and
        normalized: all addresses, all periods where the certificate
        is valid, the responsible 'Ökokontrollstellen'.

        Arguments:
            filename : path to a website, could also be an url.

        """
        html = urllib.urlopen(filename).read()
        soup = BeautifulSoup(html)

        # find the address
        addresses = []
        for a in soup.find_all('address'):
            addresses.append(list(a.stripped_strings))

        norm = []
        for a in addresses:
            norm.append([a[0], expand_address(' '.join(a[1:]))])

        # find the valid period
        periods = []
        for p in soup.find_all('div', class_='validPeriod'):
            periods.extend(list(p.stripped_strings))

        valids = []
        for p in periods:
            lst = p.split("-")
            if len(lst) == 2:
                valid_from = parse(lst[0])
                valid_until = parse(lst[1])
                valids.append((valid_from, valid_until))

        # find the responsible oeko-kontrollstelle
        controls = []
        for k in soup.find_all('div', class_='col-xs-8 col-sm-10'):
            controls.extend(list(k.stripped_strings))
        numbers = []
        for k in controls:
            numbers.extend(self.oeko_re.findall(k))

        return [norm, valids, numbers]

    def get_certificate_for_addresses(self, addresses):
        """Get certificate information from the store for some addresses.

        Arguments:
            addresses : list of normalised addresses

        """
        logging.debug("get certificate for addresss: {0}".format(addresses))
        idxs = set()
        for a in addresses:
            try:
                idxs.add(self.mapping[a])
            except KeyError:
                pass
        logging.debug("got {0} idxs for {1} addresses".format(len(idxs), len(addresses)))
        infos = []
        for i in idxs:
            infos.append(self.info_dict[i])
        logging.debug("got {0} infos".format(len(infos)))

        # postprocessing retrieved information
        result = dict()
        for info in infos:
            periods = []
            for p in info[1]:
                from_ = {'month':p[0].month, 'year':p[0].year, 'day':p[0].day}
                until = {'month':p[1].month, 'year':p[1].year, 'day':p[1].day}
                periods.append((from_, until))
            for comp, adr in info[0]:
                for a in adr:
                    if a in addresses:
                        result['numbers'] = info[2]
                        result['periods'] = periods
                        result['address'] = " ".join([comp, a])
                        break
        logging.debug("returning {0} results".format(len(result)))
        return result
    
    def check_validity(self, valids):
        """Check the validity of a certificate.

        TODO: this is outdated and needs to be refactored

        Arguments:
            valids : list with valid periods

        """
        valid = False
        today = datetime.datetime.today()
        for f, u in valids:
            if f <= today and today <= u:
                valid = True
        return valid

    def build_dict(self, directory):
        """Build dictionaries for certificates and store them.

        The certificates should be stored in a directory with
        subdirectories. Each subdirectory contains websites with
        certificate information, while directory only contains
        subdirectories.

        Arguments:
            directory : path to a directory with a given structure.

        """
        idx = 0
        info_dict = dict()
        mapping = dict()
        # iterate over all the subdirectories
        for sub in os.listdir(directory):
            sub_path = os.path.join(directory, sub)
            for name in os.listdir(sub_path):
                filename = os.path.join(directory, sub, name)
                # extract information from each file
                l = self.extract_all(filename)
                # store information in info-dict with a unique number
                # as key
                info_dict[idx] = l
                # store each key from info-dict in mapping-dict with
                # each address string as key
                for adr in l[0]:
                    for a in adr[1]:
                        mapping[a] = idx
                # next unique key
                idx += 1
        # store both dictionaries permanantly
        with open(self.filename, 'wb') as f:
            cPickle.dump([info_dict, mapping], f)
        return info_dict, mapping

# just for testing. remove later!
def main():
    to_check = sys.argv[1]
    b = BiocStore()
    info = b.extract_all(to_check)
    addresses = []
    for adr in info[0]:
        addresses.extend(adr[1])
    r = b.get_certificate_for_addresses(addresses)
    print r

if __name__ == "__main__":
    main()
