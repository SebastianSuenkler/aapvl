# MIT License
#
# Copyright (c) 2016 - 2018 XXX
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

import color_filter2
import align_points
import cv2
import os
import urllib
import cPickle
import numpy as np
import logging

"""File with example contours.

Used for a knn-like distance metric.

"""
contour_name = os.path.join(os.path.split(__file__)[0], 'contours_knn.pkl')

def load_positions(file_name):
    """Load example positions.

    Arguments:
        file_name : path to file

    """
    with open(file_name) as f:
        positions = cPickle.load(f)
    return positions

def intersect(r, pos):
    """Intersect two rectangles.

    Arguments:
        r : first rectangle
        pos : second rectangle

    """
    dx = min(r[1][0], pos[1][0]) - max(r[0][0], pos[0][0])
    dy = min(r[1][1], pos[1][1]) - max(r[0][1], pos[0][1])
    if dx > 0 and dy > 0:
        return dx * dy
    else:
        return 0

def get_shapes(img):
    """Calculate the contours of an image.

    Arguments:
        img : already loaded image

    """
    gray = cv2.cvtColor(img, cv2.COLOR_BGR2GRAY)
    thresh = cv2.threshold(gray, 200, 255, cv2.THRESH_BINARY)[1]
    cnts, _ = cv2.findContours(thresh, cv2.RETR_EXTERNAL, cv2.CHAIN_APPROX_SIMPLE)
    return cnts

def generate_train_snippets_and_store_them(image_list, file_name):
    """Generate snippets for training.

    The generated snippets are stored into countour_name.

    Arguments:
        image_list : list with image names
        file_name : not used

    """
    norms = []
    for image_name in image_list:
        img = cv2.imread(image_name)
        if img == None:
            print "could not read {0}".format(image_name)
            continue
        green_sections = color_filter2.color_filter(img)
        for i in green_sections:
            snippet = img[i[0][1]:i[1][1], i[0][0]:i[1][0]]
            contours = get_shapes(snippet)
            rects = []
            for cnt in contours:
                rects.append(cv2.boundingRect(cnt))
            mids = align_points.calculate_midpoints(rects)
            norm = align_points.normalize_points(mids, i[1][0] - i[0][0] + 1, i[1][1] - i[0][1] + 1)
            norms.append(norm)
    with open(contour_name, 'w') as f:
        cPickle.dump(norms, f)

def find_logos(image_name):
    """Find logos in an image.

    Arguments:
        image_name : name of image

    """
    with open(contour_name) as f:
        knn = cPickle.load(f)
    logos = 0

    img = cv2.imread(image_name)
    if img is None:
        # read possible url
        resp = urllib.urlopen(image_name)
        image = np.asarray(bytearray(resp.read()), dtype="uint8")
        img = cv2.imdecode(image, cv2.IMREAD_COLOR)
    if img is None:
        logging.warn("could not read {0}".format(image_name))
        return 0
    green_sections = color_filter2.color_filter(img)
    for i in green_sections:
        snippet = img[i[0][1]:i[1][1], i[0][0]:i[1][0]]
        contours = get_shapes(snippet)
        rects = []
        for cnt in contours:
            rects.append(cv2.boundingRect(cnt))
        mids = align_points.calculate_midpoints(rects)
        norm = align_points.normalize_points(mids, i[1][0] - i[0][0] + 1, i[1][1] - i[0][1] + 1)
        edists = []
        for n in knn:
            edists.append(align_points.calculate_edit_distance(n, norm))
        edists.sort()
        if np.mean(edists[:3]) <= 1.9:
            logos += 1
    return logos

# TODO: remove this. it is not neede anymore, right?
def main():
    image_list = os.listdir('/home/dorle/Dokumente/data/oeko_fall/27/jpg/screenshots/')
    image_list = map(lambda x: '/home/dorle/Dokumente/data/oeko_fall/27/jpg/screenshots/' + x, image_list)
    for image_name in image_list:
        print "{0} found logos: {1}".format(image_name, find_logos(image_name))

if __name__ == "__main__":
    main()
