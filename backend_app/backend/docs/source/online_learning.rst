.. _online:

How to use online-learning with the program
===========================================

Some classifiers and external data files can be updated on a regular
basis to obtain some kind of oneline-learning for the backend. The
modules 2, 3, 4, 7 and 9 can benefit from updating. In which way each
module can be improved by updating the classifiers or external data is
described in the following chapter. Other modules, that rely on
external data, such as modules 1, 6 and 8 can't be updated to improve
the results. Nevertheless the external data for these modules should
be kept up to date to prevent false results.

The shop, food and product classifier (modules 2, 3, 4):
--------------------------------------------------------

It is assumed, that in your workflow, you reevaluate at least some
results from the classifiers. This manual classification results
should be transfered back to the database table containing the
results. In the database table "results" the column "manual_analysis"
is for this purpose. When a manual classification result of one of the
modules 2, 3 or 4 is transfered back, the flag "validation_result"
should be set to 1. After using this information for online-learning
the flag "updated_results" is set to 1.

For a given interval (see chapter :ref:`configuration`) the program
extracts all results, for which the validation flag is 1. For all
these results the value of the manual analysis is compared to the
value of the automatic analysis for each of these modules and only
when they differ the corresponding module is refined with
online-learning on this data point.

.. _food_vocab_label:

Adding words to the food vocabulary (module 3):
-----------------------------------------------

The food classifier (module 3) uses a fixed vocabulary to classify
text. You can add more words to this vocabulary by extending the file
"food_vocab" (see chapter :ref:`configuration`). The file contains in
every line one word, like:
::
   Apfel
   Banane
   Citrone
   ...

To ensure, that your words can succesfully be used, follow this
format. The words are transformed to lower characters and the file is
assumed to be in utf-8 encoding.

.. _black_white_online:

Adding information to the white- and blacklist for ingredients (module 9):
--------------------------------------------------------------------------

Even though the white and blacklist aren't directly used to build the
statistical word model for ingredints list, they are used to find
possibly not allowed ingredients and prohibited ingredients. To add
new words to this list, you can extend the files
"ingredients_whitelist" and "ingredients_blacklist" (see chapter
:ref:`configuration`).

Both files have the following format:
::
   Milch
   Zitronen
   flüssig
   ...

So each line contains one word. If there are some words, that are only
meaningful together, you can add both words in one line. But note,
that in this case only the two words seperated with the exact same
character (e.g. space) are searched for. The words are transformed to
lower characters and the file is assumed to be in utf-8 encoding.

Adding information to several lists to check health claims (module 7):
----------------------------------------------------------------------

To detect possible health claims, several different strategies can be
used (see section :ref:`module_hc`). For each strategie several
external information is used and can be extended. There exist four
files containing the different informations: one file with possibly
used substances in health claims, one file with possibly used diseases
in health claims, one file with rejected health claims and one file
with relevant verbs. The files are described in chapter
:ref:`external_data` in more detail.

.. _substances_online:

List with substances:
^^^^^^^^^^^^^^^^^^^^^

This file should contain substances, that are often used in health
claims. The file is in the following format:
::
   Vitamin C
   Eisen
   ...

The list contains also phrases with more than one word and you can
extend this list with these too. Note that only occurences with the
excat same delimiting character (e.g. space) are searched for. The
words are transformed to lower characters and the file is assumed to
be in utf-8 encoding.

.. _diseases_online:

List with disease:
^^^^^^^^^^^^^^^^^^

This file should contain disease, that are often used in health
claims. The file is in the following format:
::
   Herzinfarkt
   rote Blutkörperchen
   ...

The list contains also phrases with more than one word and you can
extend this list with these too. Note that only occurences with the
excat same delimiting character (e.g. space) are searched for. The
words are transformed to lower characters and the file is assumed to
be in utf-8 encoding.

.. _declination_online:

List with verb declinations:
^^^^^^^^^^^^^^^^^^^^^^^^^^^^

This file contains different verbs with relevant declinations. Based
on this list, sentences with not relevant verbforms are filtered out
and not considered a possible health claim. The file is in the
following format:
::
   beitragen
   trug bei
   ...

The list contains also phrases with more than one word and you can
extend this list with these too. Note that only occurences with the
excat same delimiting character (e.g. space) are searched for. The
words are transformed to lower characters and the file is assumed to
be in utf-8 encoding.

.. _rejected_online:

List with rejected health claims:
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

This file should contain rejected health claims translated to
german. The file is in the following format:
::
   Actimirell aktiviert Abwehkräfte.
   Milchschneideling macht starke Knochen.
   ...

The list contains only phrases with multiple words. Only a simple
search is performed, where the exact wording (with delimiters and
punctuation) is found. But the words are transformed to lower characters
and the file is assumed to be in utf-8 encoding.

