.. _external_data:

External data
=============

Several modules relie on data that is externally provided. This is
data, that changes probably often and should therefore be extandable
or exchangeable. There are two different formats in which the data is
assumed, either plaintext with a single phrase per line or in a csv
format. Each file is assumed to be utf-8 encoded.

For every module, that uses external data, the format is described in
the following sections. How to change the default location of one of
the files is described in chapter :ref:`configuration`. Furthermore a
description how to extend which files to obtain online learning for
some modules is described in chapter :ref:`online`.

.. _external_postal_code:

Postal codes for address extraction (module 1):
-----------------------------------------------

The module for the extraction of addresses assumes a csv formatted
file with an assignment from postal codes to regions (Bundesland and
Kreis). An example of the assumed format is given below:
::
   PLZ2,ZUST_BUNDESLAND_STAAT,KREIS
   01067,Sachsen,"Dresden, Stadt"

Note that the first line contains header information and is ignored
therefor. The corresponding key in the configuration file is
"map_file" (see chapter :ref:`configuration`).

.. _external_food_vocab:

The food vocabulary (module 3):
-------------------------------

The food classifier (module 3) uses a fixed vocabulary to classify
text. The file is in plaintext format and contains one word per
line. An example is given below:
::
   Apfel
   Banane
   Citrone
   ...

The corresponding key in the configuration file is "food_vocab" (see
chapter :ref:`configuration`). In section :ref:`food_vocab_label` is
explained how you can add words to the vocabulary.

.. _external_legal_numbers:

Legal numbers for ecological control posts (module 6):
------------------------------------------------------

The module for the verification of traders of ecological products uses
a list of valid german "Ökonummern". These are numbers from control
posts, which control and certificate the traders. Because the control
posts change from time to time, this list should be always up to
date. The numbers are given in plaintext and an example is given
below:
::
   DE-ÖKO-001
   DE-ÖKO-003

The corresponding key in the configuration file is "legal_numbers"
(see chapter :ref:`configuration`).

.. _external_door:

EU door list (module 8):
------------------------

The module for the validation of the use of specific product names
(restricted with PDO, PGI or TSG) uses a list with certified products
(door list). This list contains every product that is registered in
the EU for PDO, PGI or TSG and can be exported from here:
http://ec.europa.eu/agriculture/quality/door/list.html The format is
csv and an example is given below:
::
   ,,,,,,,,,,,,,,
   ,,,,,,,,,,,,,,
   ,,,,,,,,,,,,,,
   Dossier Number  ,      Designation        , Country , ISO ,   Status   ,   Type    , Last relevant date ,   Product Categrory   ,      Latin Transcription     , Submission date , Publication date , Registration date , 1st Amendment date , 2nd Amendment date , 3rd Amendment date 
   PL/PGI/0005/02154,Kiełbasa piaszczańska,Poland,PL,Registered,PGI,21/11/2017,"Class 1.2. Meat products (cooked, salted, smoked, etc.)",,15/07/2016,29/06/2017,21/11/2017,,,

Note that the first four lines contain header or no information and
are ignored therefor. The corresponding key for the configuration
file is "door_list" (see chapter :ref:`configuration`).

.. _external_black_white:

White- and blacklist for ingredients (module 9):
------------------------------------------------

To perform a validation of the found ingredients of a product a white-
and a blacklist are used. It is checked, if an ingredient is already
known and allowed (contained in the whitelist), already known and
prohibited (contained in the blacklist) or unknown. Therefor a white-
and a blacklist have to be provided. The assumed format is plaintext
and an example is given below:
::
   Milch
   Zitronen
   flüssig
   ...

The corresponding keywords in the configuration file are
"ingredients_whitelist" and "ingredients_blacklist" (see chapter
:ref:`configuration`). These files can be extended with words to
obtain some kind of online learning see section
:ref:`black_white_online` for more information.
   
.. _external_hc_substances:

List with substances (module 7):
--------------------------------

For the detection of possible health claims a list with commonly used
substances in health claims should be provided. This list should
contain single words and phrases. The file is assumed to be in
plaintext and an example is given below:
::
   Vitamin C
   Eisen
   ...

The corresponding keyword in the configuration file is
"health_claim_substances" (see chapter :ref:`configuration`). This
file can be extended with words to obtain some kind of online learning
see section :ref:`substances_online` for more information.

.. _external_hc_disease:

List with disease (module 7):
-----------------------------

For the detection of possible health claims a list with commonly used
diseases in health claims should be provided. This list should contain
single words and phrases. The file is assumed to be in plaintext and
an example is given below:
::
   Herzinfarkt
   rote Blutkörperchen
   ...

The corresponding keyword in the configuration file is
"health_claim_diseases" (see chapter :ref:`configuration`). This file
can be extended with words to obtain some kind of online learning see
section :ref:`diseases_online` for more information.

.. _external_hc_declination:

List with verb declinations (module 7):
---------------------------------------

To prefilter sentences after a semantic analysis of a given text, a
list with relevant verbs for health claims should be provided. In this
file all relevant declinations of the verb has to be listed. The
declinations for one verb should be delimited by a newline and between
two verbs there can be one additional newline. This file is assumed to
be in plaintext and an example is given below:
::
   beitragen
   trägt bei
   tragen bei
   trug bei
   trugen bei
   hat beigetragen
   haben beigetragen
   wird beitragen
   werden beitragen

   haben
   hat
   haben
   hatte
   hatten

The corresponding keyword in the configuration file is
"health_claim_declination" (see chapter :ref:`configuration`). This
file can be extended with more verb declinations (see chapter
:ref:`declination_online`).

.. _external_hc_rejected:

List with rejected health claims (module 7):
--------------------------------------------

A list with rejected health claims should be provided to detect
resellers, that use exactly these health claims. The file is assumed
to be in plaintext and an example is given below:
::
   Actimirell aktiviert Abwehkräfte.
   Milchschneideling macht starke Knochen.
   ...

The corresponding keyword in the configuration file is
"health_claim_rejected" (see chapter :ref:`configuration`). This file
can be extended with words to obtain some kind of online learning see
section :ref:`rejected_online` for more information.
