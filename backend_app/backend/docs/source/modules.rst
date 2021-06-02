.. _modules:

Analysis modules
================

In this chapter each available module is described in more
detail. From this information a user should be able to decide, if a
given module suites their purpose. Additionally the used technologies
and the return values are described, so an interpretation of the
results is easier.

Address extraction (module 1):
------------------------------

To extract addresses from text, snippets of the text are cut out
around 5-digit words, that resemble german postal codes. The snippets
are then labeled with an conditional random field to extract the
single elements of a possible address, like company name, street name,
house number, postal code, city and country name.

The results are returned in form of a list containing
dictionaries. Each dictionary contains the extracted address elements,
that can be accessed through the corresponding key. The possible keys
are: Unternehmen, Strasse, PLZ, Ort, Land, Bundesland, Kreis. If for
an address one or more elements haven't been found, the corresponding
value is set to None.

Shop classifier (module 2):
---------------------------

The shop classifier consists of a pipeline of different steps (see
chapter :ref:`training_clf` for more information). The training setup
is the following: First, the given text is tokenized so that only
words with more than two letters are kept and all special characters
are discarded. These tokens are weighted with a common theme (Term
frequency inverse document frequency), stopwords are removed and on
top of this weighted bag-of-words representation a support vector
machine is trained. For predicting the class the same scheme is used.

The result is a probability score which corresponds to the distance to
the hyperplane. The default probability score where it is assumed that
a website is a shop is 50. This threshold is only used for online
learning, so that the user is free to interpret the probability score.

Foodshop classifier (module 3):
-------------------------------

The food classifier consists of a pipeline of different steps (see
chapter :ref:`training_clf` for more information). The training setup
is the following: First, the given text is tokenized so that only
words with more than two letters are kept and all special characters
are discarded. These tokens are reduced to words in a fixed vocabulary
and weighted with a common theme (Term frequency inverse document
frequency) and on top of this weighted bag-of-words representation a
support vector machine is trained. For predicting the class the same
scheme is used.

The result is a probability score which corresponds to the distance to
the hyperplane. The default probability score where it is assumed that
a website contains food is 40. This threshold is only used for online
learning, so that the user is free to interpret the probability score.

Product page classifier (module 4):
-----------------------------------

The product classifier consists of a pipeline of different steps (see
chapter :ref:`training_clf` for more information). The training setup
is the following: First, the given text is tokenized so that only
words with more than two letters are kept and all special characters
are discarded. These tokens are weighted with a common theme (Term
frequency inverse document frequency) and on top of this weighted
bag-of-words representation a support vector machine is trained. For
predicting the class the same scheme is used.

The result is a probability score which corresponds to the distance to
the hyperplane. The default probability score where it is assumed that
a website offers a specific product is 50. This threshold is only used
for online learning, so that the user is free to interpret the
probability score.

Extracting product information (module 5):
------------------------------------------

This module extracts the product number and the product name. For the
product number a regular expression is used. It is assumed, that
before the product number a commonly used word indicates, that the
following word is the product number. For the product name the title
of the website is extracted and labeled with a conditional random
field. Afterwards all words between the first positive labeled and the
last positive labeled word are returned as product name. Both return
values are strings.

Checking ecological traders (module 6):
---------------------------------------

To identify and validate ecological traders different kind of analysis
are used. First the text is searched with a regular expression if any
word contains "bio", "öko", "biologisch", "ökologisch". Afterwards a
regular expression matching the specific german "Ökonummer"
(DE-ÖKO-000) is used to extract all german "Ökonummern". These are
validated against a list with all valid "Ökonummern". The result for
this analysis is a dictionary with the keys "ads", "fake" and "legal",
containing a list each with all ad-words, all not valid "Ökonummern"
and all valid "Ökonummern" found in the text.

Furthermore if a screenshot of the website is available, it is
searched for the official EU logo, that ecological traders must
display. This analysis returns a dictionary with the key "logos" where
the number of logos is stored. This dictionary can be combined with
the dictionary from the text analysis to obtain a dictionary
containing all results relevant for this module.

.. _module_hc:

Checking health claims (module 7):
----------------------------------

To detect possible and rejected health claims there are three
strategies available. The first strategy searches only for suspicious
substances and diseases, the second strategy searches for rejected
health claims and the third strategy uses semantic parsing of language
to detect relevant relationships with suspicious substances and
diseases.

The first strategy searches the text for given substances and
diseases. The found words can occur anywhere in the text and don't
stand always in a relation to each other. Here just the occurence of
these words is enough to arouse suspicion. When at least one disease
is found in the text, a list with the substances and a list with the
diseases is returned. Else only two empty lists are returned.

The second strategy searches for already rejected health claims. These
health claims have to be in the right language and only occurences
with the exact wording and setting are found. If some rejected health
claims are found a list with these health claims is returned,
otherwise only an empty list is returned.

The third strategy uses a semantic parser to get more detailed
relations between suspicious words. Each sentence of the text is
parsed and the verb is identified. If the verb is relevant for this
context, the parsed sentence is reported. To give an additional
ranking of the reported sentences, the occurence of suspicious
substances and diseases is counted and reported along the
sentence. The return value is a list with lists containing the parsed
sentence (a dictionary with the different phrases) and a ranking
value. If no relevant verbs were found, an empty list is returned.

Checking PDO, PGI and TSG (module 8):
-------------------------------------

To identify products that are registered in the EU door list and
therefor have a certificate (PDO, PGI or TSG) the product name has to
be extracted from the website (compare module 5). In order to check a
given product name against the entries in the door list a
normalization scheme has to be applied to both sides. One complication
in this matter is that the door list contains only product names in
the original language. This can lead to worse results for products in
other languages than german, because only german words are
normalized. All other product names are just splitted on whitespace
characters and converted to lower case. For the normalization step
all words are stemmed and stop words are removed.

To search a product name in the preprocessed door list, the product
name is normalized with the german scheme and the scheme for foreign
languages. After that all possible n-grams of the product name are
searched for in the preprocessed door list and the corresponding
cerfiticate group (PDO, PGI or TSG) is returned when the product name
was found. If the product name couldn't be found in the preprocessed
door list None is returned.

Extracting ingredients (module 9):
----------------------------------

This module extracts the list of ingredients from a website. To do so
three different pretrained statistical models and a conditional random
field are used. At first, a regular expression is used to extract at
least 150 words after the word "Zutaten", which has to be in front of
a list of ingredients after EU law. Each extracted word is assigned
the probability, with which it is in a list of ingredients, the
probability, with which it is in a normal text, and the probability,
with which it is at the end of a list of ingredients. For the last
probability the context is considered too and the maximum probability
is taken. On the three assigned probabilities a conditional random
field is used to determine the end of the list of ingredients. The
return value consists of a list of results for every occurence of the
word "Zutaten" in the text. For each word a dictionary with all words
from the determined list of ingredients (key: "ingredients") and the
count of occurences of the last word as indicator for the likelihood
of this exact end (key: "count") is added to the list.

Checking BioC (module 10):
--------------------------

This module validates the EU certificate for ecological traders
against one version of the BioC database. This version is from
February 2018. To check if a trader has a certificate, this modules
takes a normalized address and looks this address up in a pre-build
dictionary. If there exists at least one certificate for this address,
the information from this certificate is returned in a dictionary. The
dictionary contains the following keys and values: "numbers": all
"Ökonummern" stored within the BioC for the found certificate;
"periods": the periods in which the certificate is valid (given by
day, month and year in a dictionary); and "address": the address that
was used in the certificate.
