.. _installation:

Installation and Requirements
=============================

This chapter gives a short overview how to install the backend of the
AAPVL-Project and on which other projects this program relies on.

How to install the program:
---------------------------

First you should make sure, that all the dependencies are
installed. If you install some dependencies manually, make sure they
are in the proper path, so they can be found.

When all dependencies are installed you only need to clone the
git-repository TODO: url here and everything should work.

List of dependencies:
---------------------

Python packages:
^^^^^^^^^^^^^^^^

These packages can be installed through the package manager of your
distro or through the python package manager pip.

* numpy
* scipy
* sklearn (version 0.18)
* mysqldb (version 1.2.3. you need to install some extra packages
  (libmysqlclient-dev and python-dev) for that. instead of using pip
  you can install it directly with :code:`aptitude install
  python-mysqldb`)
* opencv and python-opencv (you should use the official packages here:
  :code:`aptitude install opencv python-opencv`)
* nltk
* sklearn-crfsuite
* bs4
* dateutil
* sphinx (to build the documentation only. If you use virtual
  environments, sphinx has to be installed in the same virtual
  enviroment to be able to build the api reference.)

non-standard libraries and programs:
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

For installation of the non-standard libraries and programs please
follow the installation guides for that program.

* libpostal with python bindings (package name :code:`postal`):
  https://github.com/openvenues/libpostal
* ParZu: https://github.com/rsennrich/ParZu
* Zmorge model for ParZu: http://kitt.ifi.uzh.ch/kitt/zmorge/

Some notes to both programs:

libpostal:
^^^^^^^^^^

If you install libpostal into a user specific path and you want to
install the python package postal afterwards, you have to specify the
path to libpostall for the installation. With pip you can use:
::
   pip install --global-option=build_ext --global-option="-L/home/foo/libs/libpostal_build/lib" --global-option="-I/home/foo/libs/libpostal_build/include" postal

ParZu:
^^^^^^

It is sufficient to follow the installation instructions for ParZu
until step 3 first part. You don't have to use the script
:code:`install.sh`. All the components installed with this script are
not used from this program.
