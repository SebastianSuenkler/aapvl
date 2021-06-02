API reference for the AAPVL backend
===================================

The main entry point: Backend
-----------------------------

.. automodule:: backend
   :members:


This is where the magic happens: Worker
---------------------------------------

.. automodule:: worker
   :members:

The Classifier (Shop, Food, Product):
-------------------------------------

.. automodule:: classifier
   :members:

.. automodule:: probability
   :members:

The Interface to the database:
------------------------------

.. automodule:: db_interface
   :members:

Extraction of text from html:
-----------------------------

.. automodule:: preprocess
   :members:

Extracting addresses:
---------------------

.. automodule:: impressum
   :members:

.. automodule:: impressum_crf
   :members:
   
Extracting relevant product information:
----------------------------------------

.. automodule:: information_extractor
   :members:

.. automodule:: anr_matcher
   :members:
			       
.. automodule:: product_crf
   :members:
		  
Extracting text information about ecological traders:
-----------------------------------------------------

.. automodule:: oeko
   :members:
   
EU Logo recognition module:
---------------------------

.. automodule:: logos

.. automodule:: find_logos
   :members:

.. automodule:: color_filter2
   :members:

Detecting possible health claims:
---------------------------------

.. automodule:: health_claims
   :members:

.. automodule:: pos_tagger
   :members:

.. automodule:: conll_parser
   :members:

Textanalysis for protected designation of geographical origin:
--------------------------------------------------------------

.. automodule:: geoschutz_check
   :members:

Extracting ingredients list:
----------------------------

.. automodule:: ingredient_extractor
   :members:

Reconciliation with BioC information:
-------------------------------------

.. automodule:: bioc
   :members:

Utils:
------

.. automodule:: my_exceptions
   :members:

.. automodule:: simple_check
   :members:

.. automodule:: approx_str_matching
   :members:
