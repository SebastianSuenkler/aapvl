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

import numpy as np

# choose good values for insertion and deletion! define a radius in
# which two points should be considered equal and set insertion and
# deletion to a higher value. Update: everything is finetuned for
# these parameters!
insertion = 1
deletion = 1

def calculate_edit_distance(s, t):
    """Calculate the edit distance for two sequences.

    Arguments:

    s -- one sequence
    t -- other sequence

    """
    dist = np.zeros((len(s)+1, len(t)+1))
    for i in xrange(len(t)):
        dist[0][i] = i * insertion
    for j in xrange(len(s)):
        dist[j][0] = j * deletion
    for i in xrange(1, len(t)+1):
        for j in xrange(1, len(s)+1):
            dist[j][i] = min(dist[j-1][i] + insertion,
                             dist[j][i-1] + deletion,
                             dist[j-1][i-1] + np.linalg.norm(s[j-1] - t[i-1]))
    return dist[len(s)][len(t)]

# takes a list of rectangles and returns a list of the corresponding
# midpoints
def calculate_midpoints(rects):
    """Calculate midpoints for rectangles.

    Returns a list with midpoints for every rectangle.

    Arguments:

    rects -- list of rectangles

    """
    return [(x+w/2.0, y+h/2.0) for (x,y,w,h) in rects]

# normalize the points, so they are independent from the size of the
# outer rectangle
def normalize_points(ps, w, h):
    """Normalize points w.r.t. width and height.

    Arguments:

    ps -- list of points
    w -- width of the rectangle
    h -- height of the rectangle

    """
    return [np.array([x/float(w), y/float(h)]) for (x,y) in ps]


# TODO: remove this, right?
def main():
    s = [1, 2, 3, 4, 5, 6, 7]
    t = [1, 2, 3, 4, 5, 5, 7]

    print calculate_edit_distance(s, t)

if __name__ == "__main__":
    main()
