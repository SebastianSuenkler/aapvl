.. _commandline:

Commandline Interface
=====================

The commandline interface for the program allows the user to perform
multiple tasks. The single tasks can be chosen by subcommands. In the
following chapter, the description for each subcommand is shown. The
same output is available via the --help flag.

General interface:
------------------

The general interface offers subcommands for training, testing,
updating more testing and the usage of the program as tool. It
contains the following subcommands and optional arguments:

::

   usage: backend.py [-h] [--config CONFIG] [--debug]
                     {train,test,update,load,run} ...

   Backend for the AAPVL-Project.

   positional arguments:
    {train,test,update,load,run}
      train               train different classifier
      test                test different classifier directly and test different functionalities
      update              update the different classifier with new data from a directory
      load                load data from a directory into database. this can be used for testing
      run                 run the backend and process jobs

   optional arguments:
    -h, --help            show this help message and exit
    --config CONFIG       line based configuration file
    --debug               enable debug-information in log-file

The help messages for the subcommands load and run are shown here in
detail, while the help messages for the other subcommands are shown in
the following sections.

Commandline interface for subcommand load:

::

   usage: backend.py load [-h] [--modules MODULES] dir

   positional arguments:
     dir                path to directory from which all files are added to the database

   optional arguments:
     -h, --help         show this help message and exit
     --modules MODULES  comma separated list of modules that are added to the database for every file. if omitted, all modules are registered

Commandline interface for subcommand run:

::

   usage: backend.py run [-h]

   optional arguments:
     -h, --help  show this help message and exit

Interface for training:
-----------------------

Commandline interface for subcommand train:

::

   usage: backend.py train [-h] {shop,food,product,imp,prod-name} ...

   positional arguments:
     {shop,food,product,imp,prod-name}
       shop                train the shop classifier with the data from DIR
       food                train the shop classifier with the data from DIR
       product             train the product classifier with the data from DIR
       imp                 train the crf for address extraction
       prod-name           train the crf for product name extraction

   optional arguments:
     -h, --help            show this help message and exit

Commandline interface for subcommand train shop:
     
::
	
   usage: backend.py train shop [-h] dir

   positional arguments:
     dir         directory with data

   optional arguments:
     -h, --help  show this help message and exit

Commandline interface for subcommand train food:

::

   usage: backend.py train food [-h] dir

   positional arguments:
     dir         directory with data

   optional arguments:
     -h, --help  show this help message and exit

Commandline interface for subcommand train product:

::

   usage: backend.py train product [-h] dir

   positional arguments:
     dir         directory with data

   optional arguments:
     -h, --help  show this help message and exit

Commandline interface for subcommand train imp:

::

   usage: backend.py train imp [-h] x y

   positional arguments:
     x           file with addresses or titles of websites respectively in each line
     y           file with label sequences in each line corresponding to the tokens in X

   optional arguments:
     -h, --help  show this help message and exit

Commandline interface for subcommand train prod-name:

::

   usage: backend.py train prod-name [-h] x y

   positional arguments:
     x           file with addresses or titles of websites respectively in each line
     y           file with label sequences in each line corresponding to the tokens in X

   optional arguments:
     -h, --help  show this help message and exit

Interface for testing:
----------------------

Commandline interface for subcommand test:

::

   usage: backend.py test [-h] {shop,food,product,imp,prod-name} ...

   positional arguments:
     {shop,food,product,imp,prod-name}
       shop                test the shop classifier with the data from DIR
       food                test the shop classifier with the data from DIR
       product             test the product classifier with the data from DIR
       imp                 test the crf for address extraction
       prod-name           test the crf for product name extraction

   optional arguments:
     -h, --help            show this help message and exit

Commandline interface for subcommand test shop:
     
::
	
   usage: backend.py test shop [-h] dir

   positional arguments:
     dir         directory with data

   optional arguments:
     -h, --help  show this help message and exit

Commandline interface for subcommand test food:

::

   usage: backend.py test food [-h] dir

   positional arguments:
     dir         directory with data

   optional arguments:
     -h, --help  show this help message and exit

Commandline interface for subcommand test product:

::

   usage: backend.py test product [-h] dir

   positional arguments:
     dir         directory with data

   optional arguments:
     -h, --help  show this help message and exit

Commandline interface for subcommand test imp:

::

   usage: backend.py test imp [-h] x y

   positional arguments:
     x           file with addresses or titles of websites respectively in each line
     y           file with label sequences in each line corresponding to the tokens in X

   optional arguments:
     -h, --help  show this help message and exit

Commandline interface for subcommand test prod-name:

::

   usage: backend.py test prod-name [-h] x y

   positional arguments:
     x           file with addresses or titles of websites respectively in each line
     y           file with label sequences in each line corresponding to the tokens in X

   optional arguments:
     -h, --help  show this help message and exit

Interface for updating:
-----------------------

Commandline interface for subcommand update:

::

   usage: backend.py update [-h] {shop,food,product,imp,prod-name} ...

   positional arguments:
     {shop,food,product,imp,prod-name}
       shop                update the shop classifier with the data from DIR
       food                update the shop classifier with the data from DIR
       product             update the product classifier with the data from DIR

   optional arguments:
     -h, --help            show this help message and exit

Commandline interface for subcommand update shop:
     
::
	
   usage: backend.py update shop [-h] dir

   positional arguments:
     dir         directory with data

   optional arguments:
     -h, --help  show this help message and exit

Commandline interface for subcommand update food:

::

   usage: backend.py update food [-h] dir

   positional arguments:
     dir         directory with data

   optional arguments:
     -h, --help  show this help message and exit

Commandline interface for subcommand update product:

::

   usage: backend.py update product [-h] dir

   positional arguments:
     dir         directory with data

   optional arguments:
     -h, --help  show this help message and exit


