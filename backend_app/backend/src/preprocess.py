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

from bs4 import BeautifulSoup, Comment
import urllib
import logging

class Preprocessor:
    """Methods to preprocess html-files and text."""

    def beautiful_soup(self, url, links=True):
        """Extract text and title of a webpage using beautiful soup.

        With this function all the text and all links of a webpage are
        extracted.

        Arguments:
            url : url of the webpage or path to the file

        Keyword Arguments:
            links : if True, all links are extracted and added. (default: True)

        """
        logging.debug("opening and reading url %s", url)
        html = urllib.urlopen(url).read()
        soup = BeautifulSoup(html)

        # kill all script and style elements
        for script in soup(["script", "style"]):
            script.extract()    # rip it out

        comments = soup.findAll(text=lambda text:isinstance(text, Comment))
        for comment in comments:
            comment.extract()
            
        # get text
        logging.debug("extracting text")
        # text = soup.get_text()
        text = " ".join(item.strip() for item in soup.find_all(text = True))

        logging.debug("pretify text")
        # break into lines and remove leading and trailing space on each
        lines = (line.strip() for line in text.splitlines())
        # break multi-headlines into a line each
        chunks = (phrase.strip() for line in lines for phrase in line.split("  "))
        # drop blank lines
        text = '\n'.join(chunk for chunk in chunks if chunk)

        text += '\n'

        # test: add all the links
        if links:
            logging.debug("get all links and add them")
            links = []
            for link in soup.find_all('a'):
                r = link.get('href')
                if not r == None:
                    links.append(r)
            text += "\n".join(links)
        logging.debug("done with preprocessing")
        if soup.title == None:
            title = ""
        else:
            title = soup.title.string
        return text, title

    def preprocess_file(self, file_, links=True):
        """Extract all the text and title of a webpage.

        Uses :meth:`beautiful_soup` to extract all the text, all links and
        the title of a webpage.

        Arguments:
            file_ : path to the webpage.

        Keyword Arguments:
            links : if True, all links are extracted as well. (default: True)

        """
        text, title = self.beautiful_soup(file_, links)
        return text, title
