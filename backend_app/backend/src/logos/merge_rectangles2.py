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

from collections import deque
from collections import defaultdict
import sys

class Point:
    """2-dimensonal Point.

    Attributes:

    x -- x coordinate
    y -- y coordinate

    """
    def __init__(self, x, y):
        """Initialize attributes.

        Arguments:

        x -- x coordinate
        y -- y coordinate

        """
        self.x = x
        self.y = y

class Rect:
    """Rectangle.

    Attributes:

    first -- Point marking the lower left corner
    last -- Point marking the upper right corner

    """
    def __init__(self, first, last):
        """Initialize attributes.

        Arguments:

        first -- list or tuple marking the lower left corner
        last -- list or tuple marking the upper right corner

        """
        self.first = Point(first[0], first[1])
        self.last = Point(last[0], last[1])

def remove_idx(lst, idx):
    """Remove an element at specific index.

    Arguments:

    lst -- list
    idx -- index

    """
    if len(lst) == 1:
        if idx == 0:
            return []
        else:
            raise Exception('no such idx')
    if idx == 0:
        return lst[1:]
    if idx == len(lst) - 1:
        return lst[:-1]
    else:
        return lst[:idx] + lst[idx + 1:]

def bs_lt_inactivate(lst, v):
    """Search greatest element lower than v.

    The search is done only for the x coordinate of the rectangles.

    Arguments:

    lst -- list containing rectangles
    v -- x coordinate

    """
    start = 0
    end = len(lst) - 1
    while start <= end:
        mid = (start + end) /2
        if lst[mid].last.x >= v:
            end = mid - 1
        else:
            start = mid + 1
    return start - 1 if start - 1 > 0 else -1

def bs_ge_search(lst, v):
    """Search smallest element greater equal v.

    The search is done only for the x coordinate of the rectangles.

    Arguments:

    lst -- list containing rectangles
    v -- x coordinate

    """
    start = 0
    end = len(lst) - 1
    while start <= end:
        mid = (start + end) /2
        if lst[mid].last.x < v:
            start = mid + 1
        else:
            end = mid - 1
    return end + 1  if end + 1 < len(lst) - 1 else -1

def bs_gt_search(lst, mr, theta):
    """Search smallest rectangle near mr within theta.

    The search is done only for the x coordinate of the rectangles.

    Arguments:

    lst -- list containing rectangles
    mr -- reference rectangle
    theta -- threshold

    """
    start = 0
    end = len(lst) - 1
    while start <= end:
        mid = (start + end) /2
        if abs(lst[mid].last.x - mr.first.x) > theta:
            end = mid - 1
        else:
            start = mid + 1
    return end + 1 if end + 1 < len(lst) - 1 else -1

def bs_lt_search(lst, mr, theta):
    """Search greatest rectangle near mr within theta.

    The search is done only for the x coordinate of the rectangles.

    Arguments:

    lst -- list containing rectangles
    mr -- reference rectangle
    theta -- threshold

    """
    start = 0
    end = len(lst) - 1
    while start <= end:
        mid = (start + end) /2
        if abs(lst[mid].last.x - mr.first.x) <= theta:
            end = mid - 1
        else:
            start = mid + 1
    return start - 1 if start - 1 > 0 else -1

class SweepStruct:
    """Data structure for sweeping algorithm.

    Attributes:

    theta -- threshold
    lst -- sorted list of rectangles. sorted w.r.t. the x coordinate

    """
    def __init__(self, theta):
        """Initialize attributes.

        Arguments:

        theta -- threshold

        """
        self.theta = theta
        self.lst = []

    def insert(self, r):
        """Insert rectangle.

        Arguments:

        r -- rectangle
        """
        self.lst.append(r)
        self.lst.sort(key=lambda x: x.last.x)

    def inactivate_bin(self, mr):
        """Inacticate rectangles preceding mr.

        Arguments:

        mr -- rectangle

        """
        idx = bs_lt_inactivate(self.lst, mr.first.x - self.theta)
        if idx > 0:
            self.lst = self.lst[idx+1:]

    def inactivate(self, mr):
        """Inactivate rectangles preceding mr.

        Arguments:

        mr -- rectangle

        """
        for i, r in enumerate(self.lst):
            if r.last.x < mr.first.x - self.theta:
                remove_idx(self.lst, i)

    def search_bin(self, mr, matches, chain_numbers, next_new_chain, to_join, lonelies):
        """Search chains.

        Arguments:

        mr -- rectangle
        matches -- dictionary mapping from each rectangle to
        neighboring rectangles
        chain_numbers -- dictionary mapping from rectangles to chain
        numbers
        next_new_chain -- next free chain number
        to_join -- dictionary to track the chains that should be
        joined
        lonelies -- set to track rectangles outside all chains

        """
        l_idx = bs_lt_search(self.lst, mr, self.theta)
        r_idx = bs_gt_search(self.lst, mr, self.theta)

        view = self.lst[l_idx+1:r_idx] if r_idx > 0 else self.lst[l_idx+1:]

        # cur_chain = chain_numbers[mr]
        try:
            cur_chain = chain_numbers[mr]
        except KeyError:
            cur_chain = -1

        for r in view:
            if (abs(r.last.y - mr.first.y) <= self.theta or
                abs(r.first.y - mr.first.y) <= self.theta or
                abs(r.last.y - mr.last.y) <= self.theta or
                abs(r.first.y - mr.last.y) <= self.theta):
                if cur_chain >= 0:
                    # c = chain_numbers[r]
                    # if c > 0:
                    try:
                        c = chain_numbers[r]
                        if c != cur_chain:
                            to_join[c].append(cur_chain)
                            to_join[cur_chain].append(c)
                    # else:
                    except KeyError:
                        chain_numbers[r] = cur_chain
                        lonelies.remove(r)
                else:
                    lonelies.remove(mr)
                    # c = chain_numbers[r]
                    # if c > 0:
                    try:
                        c = chain_numbers[r]
                        cur_chain = c
                        chain_numbers[mr] = c
                    # else:
                    except KeyError:
                        lonelies.remove(r)
                        chain_numbers[r] = next_new_chain
                        chain_numbers[mr] = next_new_chain
                        cur_chain = next_new_chain
                        next_new_chain += 1
                matches[mr].append(r)
                matches[r].append(mr)

        j_idx = bs_ge_search(self.lst, mr.first.x)

        view2 = self.lst[j_idx:]
        for r in view2:
            if (r.last.y >= mr.first.y and
                r.first.x <= mr.first.x and
                r.first.y <= mr.first.y):
                if cur_chain >= 0:
                    # c = chain_numbers[r]
                    # if c > 0:
                    try:
                        c = chain_numbers[r]
                        if c != cur_chain:
                            to_join[c].append(cur_chain)
                            to_join[cur_chain].append(c)
                    # else:
                    except KeyError:
                        chain_numbers[r] = cur_chain
                        lonelies.remove(r)
                else:
                    try:
                        lonelies.remove(mr)
                    except KeyError:
                        print mr
                        print cur_chain
                        print chain_numbers
                        sys.exit(1)
                        # c = chain_numbers[r]
                    # if c > 0:
                    try:
                        c = chain_numbers[r]
                        cur_chain = c
                        chain_numbers[mr] = c
                    # else:
                    except KeyError:
                        chain_numbers[r] = next_new_chain
                        chain_numbers[mr] = next_new_chain
                        cur_chain = next_new_chain
                        next_new_chain += 1
                        lonelies.remove(r)
                matches[mr].append(r)
                matches[r].append(mr)

        return matches, chain_numbers, next_new_chain, to_join, lonelies

    def search(self, mr, matches):
        """Search for chains.

        NOT USED!!!

        Arguments:

        mr -- rectangle
        matches -- dictionary mapping from each rectangle to all
        neighboring rectangles

        """
        for r in self.lst:
            # checks for match because of distance
            if (abs(r.last.x - mr.first.x) <= self.theta and
                (abs(r.last.y - mr.first.y) <= self.theta or
                 abs(r.first.y - mr.first.y) <= self.theta or
                 abs(r.last.y - mr.last.y) <= self.theta or
                 abs(r.first.y - mr.last.y) <= self.theta)):
                matches[mr].append(r)
                matches[r].append(mr)
            elif (r.last.x >= mr.first.x and
                  r.last.y >= mr.first.y and
                  r.first.x <= mr.first.x and
                  r.first.y <= mr.first.y):
                matches[mr].append(r)
                matches[r].append(mr)

        return matches

def get_merge_points(lst, theta=1):
    """Calculate chains and mandatory joins for chains.

    Arguments:

    lst -- list of rectangles to join

    Optional Keyword Argument:

    theta -- threshold (default: 1)

    """
    lst.sort(key=lambda x: x.first.x)
    sweep = SweepStruct(theta)
    matches = defaultdict(list)
    lonelies = set(lst)
    chain_numbers = dict()
    next_new_chain = 0
    to_join = defaultdict(list)
    for r in lst:
        matches[r] = []
        sweep.inactivate_bin(r)
        matches, chain_numbers, next_new_chain, to_join, lonelies = sweep.search_bin(r, matches, chain_numbers,
                                                                                     next_new_chain, to_join, lonelies)
        sweep.insert(r)
    return matches, chain_numbers, to_join, lonelies

def get_chains(chain_numbers, to_join):
    """Extract chains from information.

    Arguments:

    chain_numbers -- dictionary mapping from rectangle to chain number
    to_join -- dictionary indicating chains, that should be joined

    """
    chains_idx = dict()
    chains = []
    for k, v in chain_numbers.iteritems():
        if v < 0:
            chains.append([k])
        try:
            idx = chains_idx[v]
            chains[idx].append(k)
        except KeyError:
            chains_idx[v] = len(chains)
            chains.append([k])
    # print "len(chains) before filtering: {0}".format(len(chains))
    # for k, l in to_join.iteritems():
    #     for v in l:
    #         # TODO: maybe i have to choose the smaller idx to join into!
    #         k_idx = chains_idx[k]
    #         v_idx = chains_idx[v]
    #         if k_idx == v_idx:
    #             continue
    #         chains[k_idx].extend(chains[v_idx])
    #         chains[v_idx] = []
    #         chains_idx[v] = k
    # chains = filter(lambda x: len(x) > 0, chains)
    return chains

def get_components_fast(matches):
    """Compute connected components for matches.

    Arguments:

    matches -- dictionary mapping from rectangles to chain_numbers

    """
    print len(matches)
    set_idx = dict()
    idx = 0
    sets = []
    for k, v in matches.iteritems():
        try:
            i = set_idx[k]
            for e in v:
                sets[i].add(e)
                set_idx[e] = i
        except KeyError:
            set_idx[k] = idx
            sets.append(set([k]))
            for e in v:
                sets[idx].add(e)
                set_idx[e] = idx
            idx += 1
    len_ = 0
    for s in sets:
        len_ += len(s)
    print len_
    return sets

def get_connected_components(matches):
    """Compute connected components for matches.

    Arguments:

    matches -- dictionary mapping from rectangles to chain numbers

    """
    # the matches lists span a graph. search this graph with bfs to
    # get connected components
    queue = deque()
    visited = set()
    components = defaultdict(list)
    comp = 0
    for k, v in matches.iteritems():
        if k in visited:
            continue
        visited.add(k)
        # start the breadth-first search
        components[comp].append(k)
        for c in v:
            queue.append(c)
        while len(queue) > 0:
            w = queue.popleft()
            visited.add(w)
            components[comp].append(w)
            children = matches[w]
            for c in children:
                if c in visited:
                    continue
                queue.append(c)
        comp += 1
    return components

def get_large_rects_chain(chains):
    """Transform the chains into large bounding rectangles.

    Arguments:

    chains -- list of chains

    """
    large_rects = []
    for chain in chains:
        # print "handling component:"
        # print_fun_rect(k)
        max_ = chain[0]
        for r in chain[1:]:
            if r.first.x < max_.first.x:
                max_.first.x = r.first.x
            if r.first.y < max_.first.y:
                max_.first.y = r.first.y
            if r.last.x > max_.last.x:
                max_.last.x = r.last.x
            if r.last.y > max_.last.y:
                max_.last.y = r.last.y
        large_rects.append(max_)
    return large_rects

def get_large_rects(components):
    """Compute large bounding rectangles for connected components.

    Arguments:

    components -- dictionary containing connected components

    """
    large_rects = []
    for k, v in components.iteritems():
        # print "handling component:"
        # print_fun_rect(k)
        max_ = v[0]
        for r in v[1:]:
            if r.first.x < max_.first.x:
                max_.first.x = r.first.x
            if r.first.y < max_.first.y:
                max_.first.y = r.first.y
            if r.last.x > max_.last.x:
                max_.last.x = r.last.x
            if r.last.y > max_.last.y:
                max_.last.y = r.last.y
        large_rects.append(max_)
    return large_rects

def print_fun_rect(r):
    """Print rectangle to stdout.

    Arguments:

    r -- rectangle

    """
    sys.stdout.write("R(({0}, {1}), ({2}, {3})), ".format(r.first.x, r.first.y, r.last.x, r.last.y))

# this is not needed, right?
def main():
    rects = [Rect((0,0), (2,2)),
             Rect((4,3), (5,4)),
             Rect((1,1), (3,3)),
             Rect((7,3), (8,4)),
             Rect((9,2), (10,3)),
             Rect((20,0), (35,5)),
             Rect((25,2), (26,3)),
             Rect((30,3), (40,10))]
    matches, chain_numbers, to_join = get_merge_points(rects)
    print len(matches)
    for k, v in chain_numbers.iteritems():
        print_fun_rect(k)
        sys.stdout.write(": {0}\n".format(v))
    for k, v in to_join.iteritems():
        sys.stdout.write("{0}: {1}\n".format(k, v))
    comps = get_connected_components(matches)
    print len(comps)
    large_rects = get_large_rects(comps)
    print len (large_rects)
    for k in large_rects:
        print k.first.x, k.first.y, k.last.x, k.last.y

if __name__ == "__main__":
    main()
