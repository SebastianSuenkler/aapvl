First steps with the program
============================

This chapter gives a short overview and some simple examples how to
use the program. With the general flags :code:`config` and
:code:`debug` can be used to provide another config file or to get
more information about each module in the log. In the following
examples they are omitted.

Testing the setup:
------------------

To test the proper setup of all libraries, models and external data
files, you can use the simple test. You can invoke this test as
follows:
::
   python src/backend.py test simple

Some example files are loaded to the database and all modules are run
on every file.

Invoking the backend:
---------------------

You can invoke the backend, so that every job, that is added to the
database, will be processed.
::
   python src/backend.py run

In the configuration you can choose how often the backend should look
for jobs in the database and when the backend should shut itself down.

Training a classifier:
----------------------

You can retrain a single classifier, e.g. the shop classifier, like
this:
::
   python src/backend.py train shop path/to/data

The directory :code:`path/to/data` has to contain two subdirectories
:code:`0` and :code:`1`, which contain the single data
files. :code:`0` is interpreted as the positive class and :code:`1` as
the negative class. See chapter :ref:`training` for more information.

Training a crf:
---------------

Two modules use conditional random fields. You can train these two
conditional random fields, e.g. the one used to extract addresses,
like this:
::
   python src/backend.py train imp addresses.txt labels.txt

The file :code:`addresses.txt` contains one address per line and in
:code:`labels.txt` in each line a label for each word is
contained. See chapter :ref:`training` for more information.

Testing a classifier:
---------------------

To test one of the classifiers (using conditional random fields or
support vector machines), you can use:
::
   python src/backend.py test shop path/to/data

Updating a classifier:
----------------------

When you obtain additional data and want to update one of the suport
vector machine using classifiers, you can use this subcommand to
update the classifier directly with data from a directory.
::
   python src/backend.py update shop path/to/data

The directory has to conform with the assumptions. See chapter
:ref:`training` for more information.

   
Loading data in db:
-------------------

To test not only the setup but to test a broder spectrum you can load
some files into the database with a specific selection of modules
registered for these files. With
::
   python src/backend.py load --modules "1,2,3" path/to/data

you load all files in :code:`path/to/data` in the database and
register the modules 1, 2 and 3 for them. With running
::
   python src/backend.py run

you can process these files. Note that this is just for testing
purposes and some functionality, like e.g. the information summary for
all subpages, is not available here.

