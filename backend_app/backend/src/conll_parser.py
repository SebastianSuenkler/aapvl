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

# inspired and code-parts taken from https://github.com/EmilStenstrom/conllu
import re
from collections import defaultdict

class Entry:
    """Contains important information for a conll entry

    Attributes:
        id_ : id of the entry
        form : actual word
        pos : POS-tag
        xpos : STTS-POS-tag
        head : id of parent node
        dep : relationship to parent node

    """
    def __init__(self, id_, form, pos, xpos, head, dep):
        """Initialize entry.

        Arguments:
            id_ : id of entry
            form : actual word
            pos : POS-tag
            xpos : STTS-POS-tag
            head : id of parent node
            dep : relationship to parent node

        """
        self.id_ = id_
        self.form = form
        self.pos = pos
        self.xpos = xpos
        self.head = head
        self.dep = dep

    def __str__(self):
        """Return printable form."""
        return "Entry(id: {0}, form: {1}, pos: {2}, xpos: {3}, head: {4}, dep: {5})".format(self.id_, self.form, self.pos, self.xpos, self.head, self.dep)

    def __repr__(self):
        """Return unicode printable form."""
        return "Entry(id: {0}, form: {1}, pos: {2}, xpos: {3}, head: {4}, dep: {5})".format(self.id_, self.form, self.pos, self.xpos, self.head, self.dep)

class Node:
    """Node for conll parser tree.

    Attributes:
        data : data of the represented row
        lchilds : list of left childs, sorted by :attr:`self.data.id_`
        rchilds : list of right childs, sorted by :attr:`self.data.id_`

    """
    def __init__(self, data):
        self.data = data
        self.lchilds = []
        self.rchilds = []

    def __str__(self):
        """Return printable form."""
        return "{0}, {1}, {2}".format(self.data.id_, self.lchilds, self.rchilds)

    def __repr__(self):
        """Return printable form"""
        return "{0}, {1}, {2}".format(self.data.id_, self.lchilds, self.rchilds)

    def add_rchild(self, id_):
        """Add a right child to this node.

        This function assumes, that the actual node is already
        initialized. Only the id is added to the list of right
        children.

        Arguments:
            id_ : id of the node to add

        """
        self.rchilds.append(id_)
        self.rchilds.sort()

    def add_lchild(self, id_):
        """Add a left child to this node.

        This function assumes, that the actual node is already
        initialized. Only the id is added to the list of left
        children.

        Arguments:
            id_ : id of the node to add

        """
        self.lchilds.append(id_)
        self.lchilds.sort()

    def add_child(self, id_):
        """Add a child to this node.

        This function assumes, that the actual node is already
        initialized. Only the id is added to one of the list of
        children.

        Arguments:
            id_ : id of the node to add

        """
        if id_ < self.data.id_:
            self.add_lchild(id_)
        else:
            self.add_rchild(id_)

    def get_children(self):
        """Return a list with all children."""
        return self.lchilds + self.rchilds

    def get_dep(self):
        """Return the dependency relation retrieved from :attr:`self.data`."""
        return self.data.dep

class Tree:
    """Dependency tree for one sentence.

    This structure models the dependency relations of single phrases
    found with an dependency parser. It is constructed from the
    information in the conll format.

    Attributes:
        root : id of root node (always 0)
        nodes : list of nodes

    """
    def __init__(self, nodes):
        """Initialize tree.

        This function only adds all nodes without dependencies to the
        tree. The structure has to be formed with
        :meth:`create_tree_structure`.

        Arguments:
            nodes : list of entries

        """
        self.root = 0
        self.nodes = [Node(Entry(0, '-', '-', '-', '-', '-'))]
        self.nodes.extend(map(lambda x: Node(x), sorted(nodes, key=lambda x: x.id_)))

    def __str__(self):
        """Return printable form."""
        struct = defaultdict(list)
        for n in self.nodes:
            struct[n.data.id_].extend(n.lchilds)
            struct[n.data.id_].extend(n.rchilds)
        return "nodes:{0},\nstruct:{1}".format(self.nodes, struct)

    def __repr__(self):
        """Return printable form."""
        struct = defaultdict(list)
        for n in self.nodes:
            struct[n.data.id_].extend(n.lchilds)
            struct[n.data.id_].extend(n.rchilds)
        return "nodes:{0},\nstruct:{1}".format(self.nodes, struct)

    def create_tree_structure(self, head_indexed):
        """Create the dependency structure.

        This function creates the given dependency relationship
        between single nodes within the tree.

        Arguments:
            head_indexed : dictionary mapping id to a list of children

        """
        keys = sorted(head_indexed.keys())
        for k in keys:
            xs = head_indexed[k]
            for x in xs:
                self.nodes[k].add_child(x)

    def get_subtree(self, id_):
        """Return subtree under a node.

        Arguments:
            id_ : id of the root for the subtree

        """
        return (self.get_leftest_child(id_), self.get_rightest_child(id_))

    def get_leftest_child(self, id_):
        """Return leftest child under a node.

        Arguments:
            id_ : id of the node

        """
        n = self.nodes[id_]
        if len(n.lchilds) == 0:
            return id_
        else:
            return self.get_leftest_child(n.lchilds[0])

    def get_rightest_child(self, id_):
        """Return rightest child under a node.

        Arguments:
            id_ : id of the node

        """
        n = self.nodes[id_]
        if len(n.rchilds) == 0:
            return id_
        else:
            return self.get_rightest_child(n.rchilds[-1])

    def get_rightest_noun_part(self, id_):
        """Return the rightest part of a noun phrase.

        Expands the noun phrase starting at a node heuristically to
        the right.

        Arguments:
            id_ : id of the node

        """
        dep_labels = ['subj', 'obja', 'attr', 'gmod', 'kon', 'cj', 'pred', 'objg', 'app']
        pos_labels = ['N']

        rbracket = 0
        n = self.nodes[id_]
        if n.data.pos in pos_labels and n.data.dep in dep_labels:
            rbracket = id_+1
            # try to extend the span to the right
            # to capture close apposition/measurement constructions
            for rid_ in n.rchilds:
                r = self.nodes[rid_]
                if r.data.pos in pos_labels and r.data.dep == "app":
                    rbracket = rid_+1
        return rbracket

    def string_from_to(self, i, j):
        """Return the string in the intervall from i to j.

        Arguments:
            i : id of left border
            j : id of right border

        """
        s = []
        for n in self.nodes[i:j]:
            s.append(n.data.form)
        if len(s) > 0:
            return ' '.join(s)

        
def parse(text):
    """Parse text and return a list of lists.

    Arguments:
        text : conll formated text for possibly more than one sentence

    """
    return [[parse_line(line) for line in sentence.split("\n")
             if line and not line.strip().startswith("#")]
            for sentence in text.decode('utf-8').split("\n\n") if sentence]

def parse_tree(text):
    """Parse text into dependency trees.

    A list of trees is created containing one tree per sentence.

    Arguments:
        text : conll formated text for possibly more than one sentence

    """
    result = parse(text)

    trees = []
    for sentence in result:
        head_indexed = defaultdict(list)
        nodes = []
        for entry in sentence:
            nodes.append(entry)
            head_indexed[entry.head].append(entry.id_)

        t = Tree(nodes)
        t.create_tree_structure(head_indexed)
        trees.append(t)

    return trees

def parse_line(line):
    """Parse one line of conll format and return Entry.

    Arguments:
        line : one line of conll format
    """
    line = re.split(r"\t| {1,}", line)

    id_ = parse_int_value(line[0])
    form = line[1]
    pos = line[3]
    xpos = line[4]
    head = parse_int_value(line[6])
    dep = line[7]

    return Entry(id_, form, pos, xpos, head, dep)

def parse_int_value(value):
    """Parse int from string handling malformed ints.

    Arguments:
        value : value to parse

    """
    if value.isdigit():
        return int(value)

    return None

data = """
1	Ruth	Ruth	N	NE	Masc|Nom|Sg	14	subj	_	_ 
2	Gabriel	Gabriel	N	NE	Masc|Nom|Sg	1	app	_	_ 
3	,	,	$,	$,	_	0	root	_	_ 
4	die	die	ART	ART	Def|Fem|Nom|Sg	5	det	_	_ 
5	Tochter	Tochter	N	NN	Fem|Nom|Sg	2	app	_	_ 
6	der	die	ART	ART	Def|Fem|Gen|Sg	7	det	_	_ 
7	Schriftstellerin	Schriftstellerin	N	NN	Fem|Gen|Sg	5	gmod	_	_ 
8	und	und	KON	KON	_	7	kon	_	_ 
9	Schauspielerin	Schauspielerin	N	NN	Fem|Gen|Sg	8	cj	_	_ 
10	Ana	Ana	N	NE	Fem|Gen|Sg	9	app	_	_ 
11	Maria	Maria	N	NE	Fem|Gen|Sg	10	app	_	_ 
12	Bueno	Bueno	N	NE	Fem|Gen|Sg	11	app	_	_ 
13	,	,	$,	$,	_	0	root	_	_ 
14	wurden	werden	V	VAFIN	_|Pl|Past|Ind	0	root	_	_ 
15	in	in	PREP	APPR	_	18	pp	_	_ 
16	San	San	N	NE	_|_|_	15	pn	_	_ 
17	Fernando	Fernando	N	NE	_|_|_	16	app	_	_ 
18	geboren	gebären	V	VVPP	_	14	aux	_	_ 
19	.	.	$.	$.	_	0	root	_	_ 
"""

data2 ="""
1	Zum	zu	PREP	APPRART	Dat	3	pp	_	_ 
2	Essen	Esse	N	NN	_|Dat|_	1	pn	_	_ 
3	hätte	haben	V	VAFIN	1|Sg|Past|Subj	0	root	_	_ 
4	ich	ich	PRO	PPER	1|Sg|_|Nom	3	subj	_	_ 
5	gerne	gerne	ADV	ADV	_	3	adv	_	_ 
6	eine	eine	ART	ART	Indef|Fem|_|Sg	7	det	_	_ 
7	Tasse	Tasse	N	NN	_|_|Sg	3	obja	_	_ 
8	Tee	Tee	N	NN	_|_|Sg	7	app	_	_ 
9	.	.	$.	$.	_	0	root	_	_ 
"""

data3 ="""
1	Zuckerfreier	zuckerfrei	ADJA	ADJA	Pos|Masc|Nom|Sg|St|	2	attr	_	_ 
2	Kaugummi	Kaugummi	N	NN	Masc|Nom|Sg	3	subj	_	_ 
3	trägt	tragen	V	VVFIN	3|Sg|Pres|Ind	0	root	_	_ 
4	zur	zu	PREP	APPRART	Dat	3	objp	_	_ 
5	Erhaltung	Erhaltung	N	NN	Fem|Dat|Sg	4	pn	_	_ 
6	der	die	ART	ART	Def|Fem|Gen|Sg	7	det	_	_ 
7	Zahnmineralisierung	zahnmineralisierenung	N	NN	Fem|Gen|Sg	5	gmod	_	_ 
8	bei	bei	PTKVZ	PTKVZ	_	3	avz	_	_ 
9	.	.	$.	$.	_	0	root	_	_ 
"""

data4 ="""
1	Zum	zu	PREP	APPRART	Dat	3	pp	_	_ 
2	Essen	Esse	N	NN	_|Dat|_	1	pn	_	_ 
3	hätte	haben	V	VAFIN	1|Sg|Past|Subj	0	root	_	_ 
4	ich	ich	PRO	PPER	1|Sg|_|Nom	3	subj	_	_ 
5	gerne	gerne	ADV	ADV	_	3	adv	_	_ 
6	eine	eine	ART	ART	Indef|Fem|_|Sg	7	det	_	_ 
7	Tasse	Tasse	N	NN	_|_|Sg	3	obja	_	_ 
8	Tee	Tee	N	NN	_|_|Sg	7	app	_	_ 
9	.	.	$.	$.	_	0	root	_	_ 

1	Zuckerfreier	zuckerfrei	ADJA	ADJA	Pos|Masc|Nom|Sg|St|	2	attr	_	_ 
2	Kaugummi	Kaugummi	N	NN	Masc|Nom|Sg	3	subj	_	_ 
3	trägt	tragen	V	VVFIN	3|Sg|Pres|Ind	0	root	_	_ 
4	zur	zu	PREP	APPRART	Dat	3	objp	_	_ 
5	Erhaltung	Erhaltung	N	NN	Fem|Dat|Sg	4	pn	_	_ 
6	der	die	ART	ART	Def|Fem|Gen|Sg	7	det	_	_ 
7	Zahnmineralisierung	zahnmineralisierenung	N	NN	Fem|Gen|Sg	5	gmod	_	_ 
8	bei	bei	PTKVZ	PTKVZ	_	3	avz	_	_ 
9	.	.	$.	$.	_	0	root	_	_ 
"""

def split_tree(t):
    """Split tree at root node into relevant phrases.

    This function returns the intervalls of the phrases.

    Arguments:
        t : tree to split

    """
    verb_deps = ['aux', 'avz']
    verb_mod_deps = ['objc', 'pp', 'pn', 'pred', 'adv', 'cj', 'eth', 'kom', 'kon', 'konj', 'neb', 'obji', 'part', 'rel']
    np_deps = ['expl', 'gmod', 'grad', 'np2', 'obj', 'obja', 'obja2', 'objd', 'objg', 'objp', 'subj']
    verb_id = 0
    verbs = []
    verb_modifier = []
    nps = []
    ps = []
    for c in t.nodes[0].get_children():
        if t.nodes[c].data.pos == 'V':
            verb_id = c
            verbs.append(c)
    for c in t.nodes[verb_id].get_children():
        if t.nodes[c].get_dep() in verb_deps:
            verbs.append(c)
    for v in verbs:
        for c in t.nodes[v].get_children():
            dep = t.nodes[c].get_dep()
            if dep not in verb_deps:
                if dep in verb_mod_deps:
                    verb_modifier.append(c)
                elif dep in np_deps:
                    nps.append(c)
                else:
                    ps.append(c)
    return verbs, verb_modifier, nps, ps
            
def get_relation(t):
    """Splits tree at root and returns the relevant strings.

    Arguments:
        t : tree to split
    """
    relation = dict()
    verbs, verb_modifier, nps, ps = split_tree(t)
    relation["verb"] = [t.nodes[v].data.form for v in verbs]
    relation["np"] = [t.string_from_to(i, j+1) for (i, j) in [t.get_subtree(n) for n in nps]]
    relation["modifier"] = [t.string_from_to(i, j+1) for (i, j) in [t.get_subtree(m) for m in verb_modifier]]
    return relation

def main():
    trees = parse_tree(data2)
    for t in trees:
        last = 0
        for i, n in enumerate(t.nodes):
            if i < last:
                continue
            if n.data.pos in ["N"]:
                first = t.get_leftest_child(n.data.id_)
                last = t.get_rightest_noun_part(n.data.id_)
                t.print_string_from_to(first, last)

if __name__ == "__main__":
    main()
