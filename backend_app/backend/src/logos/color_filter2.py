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

import cv2
import os
import numpy as np
import sys
import merge_rectangles2
from collections import defaultdict

def area(r):
    """Calculate area of rectangle.

    Arguments:
        r : rectangle

    """
    w = r[1][0] - r[0][0]
    h = r[1][1] - r[0][1]
    return w * h

def ratio(r):
    """Calculate width to height ratio.

    Arguments:
        r : rectangle

    """
    w = r[1][0] - r[0][0]
    h = r[1][1] - r[0][1]
    return w / float(h)

def merge_rects(rects):
    """Merge intersecting rectangles.

    Arguments:
        rects : list of rectangles

    """
    theta = 0
    rects.sort(key=lambda x:x.first.x)
    matches, chain_numbers, to_join, lonelies = merge_rectangles2.get_merge_points(rects, theta)
    chains = merge_rectangles2.get_chains(chain_numbers, to_join)

    large_rects = merge_rectangles2.get_large_rects_chain(chains)

    big_rects = []
    for v in large_rects:
        big_rects.append([(v.first.x, v.first.y), (v.last.x, v.last.y)])
    for v in lonelies:
        big_rects.append([(v.first.x, v.first.y), (v.last.x, v.last.y)])
    only_big_rects = filter(lambda x: area(x) > 350 and area(x) < 47500 and ratio(x) > 1.35 and ratio(x) < 1.85, big_rects)

    return only_big_rects

def is_rect_shape(c):
    """Check if contour is approximately rectangular.

    Arguments:
        c : contour

    """
    peri = cv2.arcLength(c, True)
    approx = cv2.approxPolyDP(c, 0.04 * peri, True)
    if len(approx) == 4:
        return True
    else:
        return False

def color_filter(img):
    """Apply color filter to img.

    Returns all rectangles within which the color is according to the
    color filter.

    Arguments:
        img : image in opencv image data type

    """
    hsv_img = cv2.cvtColor(img, cv2.COLOR_BGR2HSV)
    color_min = np.array([35, 50, 50], np.uint8)
    color_max = np.array([45, 255, 255], np.uint8)
    only_green = cv2.inRange(hsv_img, color_min, color_max)
    orig = cv2.bitwise_and(img, img, mask=only_green)
    gray = cv2.cvtColor(orig, cv2.COLOR_BGR2GRAY)
    thresh = cv2.threshold(gray, 150, 255, cv2.THRESH_BINARY)[1]
    contours, hierarchy = cv2.findContours(thresh, cv2.RETR_TREE, cv2.CHAIN_APPROX_SIMPLE)
    rects = []
    cv_rects = filter(lambda x: is_rect_shape(x), contours)
    for r in cv_rects:
        x,y,w,h = cv2.boundingRect(r)
        rects.append([(x,y), (x+w, y+h)])
    rects = filter(lambda x: area(x) > 350 and area(x) < 47500 and ratio(x) > 1.35 and ratio(x) < 1.85, rects)
    return rects

# TODO: this isn't needed anymore, right?
def main():
    image_list = os.listdir('../pictures/screenshots_biologo_dorle_complete')
    image_list = map(lambda x: '../pictures/screenshots_biologo_dorle_complete/' + x, image_list)
    # image_list = ["../pictures/screenshots_biologo_dorle_complete/Bildschirmfoto von 2017-07-06 09-58-44.png"]
    # minimums, maximums = process_images(image_list)
    # print "b: ({0}, {1})".format(min(minimums['b']), max(maximums['b']))
    # print "r: ({0}, {1})".format(min(minimums['r']), max(maximums['r']))
    # print "g: ({0}, {1})".format(min(minimums['g']), max(maximums['g']))
    filter_images(image_list)

if __name__ == "__main__":
    main()
