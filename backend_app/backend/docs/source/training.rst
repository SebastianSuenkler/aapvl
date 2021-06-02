.. _training:

How to train and test the single classifiers
============================================

This chapter explains the necessary files and formats to train or test
the single classifiers. The programs offers a commandline interface
for this task, which is explained in chapter :ref:`commandline`. Some
modules presented in chapter :ref:`modules` use classifiers to analyse
and extract data. These modules are the shop (module 2), food
(module 3) and product (module 4) classifier, the address extraction
(module 1) and the product name extraction (module 5) modules.

If you want to measure the performance of more than one module you
should add single jobs to the database and let the program run
normally (see chapter XX and XX).

.. _training_clf:

Shop, Food and Product classifier (modules 2, 3, 4):
----------------------------------------------------

The classifier for the shop, food and product domain consists of a
support vector machine with a bag-of-words and a random forest as
feature selection. For each domain some parameters were chosen by
cross-validation on a specific training data set and are hard-coded in
the program. So they can not easily be changed or evaluated. The
parameters relate to the feature extraction, the feature selection and
a hyperparameter from the support vector machine.

The chosen parameters for each domain are:

+-----------------+-------------------+---------+---------+------------+
|**pipeline step**|**parameter**      |**shop** |**food** |**product** |
+=================+===================+=========+=========+============+
|                 | stop-word removal |  yes    | no      | no         |
| tfidf           +-------------------+---------+---------+------------+
|                 | fixed vocabulary  |  no     | yes     | no         |
+-----------------+-------------------+---------+---------+------------+
|                 | nof estimators    | 100     | 0       | 100        |
| random forest   +-------------------+---------+---------+------------+
|                 | threshold         | 0.0004  | 0       | 0.0004     |
+-----------------+-------------------+---------+---------+------------+
| svm             | alpha             | 0.00001 | 0.0001  | 0.0000001  |
+-----------------+-------------------+---------+---------+------------+

To train a classifier for one of the three domains, a training set is
needed. It should consist of either texts from webpages in utf-8
encoding or directly of stored webpages. For each webpage in the
training set the corresponding class should be manually assigned
(e.g. shop or no shop) for better performance of the trained
classifier.

A special directory structure is assumed by the training and testing
methods of the classifier. A directory with two subdirectories
containing files for each class should be provided, like so:
::
  train/
    0/
      file1.html
      file2.html
      file3.html
      ...
    1/
      file4.html
      file5.html
      file6.html
      ...

To obtain the right measurements while testing, all positive examples
(like shop, food or product) should be within the directory 0/ while
all negative examples (like no shop, no food or no product) should be
within the directory 1/. The calculation of precision and recall is
based on the assumption, that the classes are assigned this way.

In general the performance of the classifier can be measured with a
test set in the same directory structure as the training set. The test
set should not be used for training.

Address extraction (module 1):
------------------------------

The extraction of addresses is learned with a conditional random field
(CRF). To train a CRF a set of labeled sequences is needed. Each
sequence contains text seperated by whitespace (except newline)
characters and to each token a label has to be assigned. When a token
is irrelevant the label OT (other) can be assigned. The module
extracts the tokens with the following labels: FN (company name), ST
(street), NR (house number), PLZ (postal code), CI (city) and CO
(country).

To train a CRF one file with the text (x-file) and one file with the
labels (y-file) has to be provided. The files can look somewhat like
this:
::
   x-file.txt
     Hier sitzt die Firma: Musterman AG A-Straße 123 98765 Krautheim komm vorbei
     Trifft dich Hansi Hinterseer B-Straße 456 12345 Alpenort Deutschland schönes Date!

   y-file.txt
     OT OT OT OT FN FN ST NR PLZ CI OT OT
     OT OT OT OT ST NR PLZ CI CO OT OT

Note: The lines of text correspond with the lines of labels and not
every label has to be present in every sequence (e.g. the first line
contains no CO label and in the second example "Hansi Hinterseer" is a
name of a person and not a company, so not labeled FN). But you should
only add examples you want the CRF to recognize to your training data
set. So leave out all the address fragments, when you don't want to
extract them too.

To test the performance of your CRF you can provide an unseen x-file
and corresponding y-file to the classifier. There is functionality in
the program to measure the performance of the trained CRF for every
label.

Product name extraction (module 5):
-----------------------------------

Like the address extraction, the product name extraction uses a CRF to
label a token sequence. The label set consists of two different
labels: OT (other) and AN (article name). The input to the pretrained
CRF were titles of webpages and their manually assigned labels. The
titles were tokenized, so that only words of minimum length 2 and
special characters are extracted. You should tokenize your input for
training or testing in a similar way.

An example input looks like this:
::
   x-file.txt
     Amore kaufen : Bratwurst Henning 180 gr - nur hier
     laden online - salzige gurken , lose
   y-file.txt
     OT OT OT AN AN AN AN OT OT OT
     OT OT OT AN AN AN AN
   
To test the performance of your CRF you can provide an unseen x-file
and corresponding y-file to the classifier. There is functionality in
the program to measure the performance of the trained CRF for every
label.



