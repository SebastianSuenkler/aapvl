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

import codecs
import os
import argparse
import worker
import db_interface
import classifier
import logging
import datetime
import json
import impressum_crf
import product_crf
import sys

def train_clf(config, directory, type_):
    """Train the different classifiers.

    Arguments:
        directory : path to directory with training data
        type_ : type of the classifier. one of: 'shop', 'food' or 'product'

    """
    # trains the classifier and stores the results immediatly
    clf = classifier.Classifier(config, directory = directory, new = True, type_ = type_)

def train_impressum(x_file, y_file):
    """Train the impressum crf.

    Arguments:
        x_file : file with training sequences
        y_file : file with training labels for the sequences

    """
    clf = impressum_crf.ImpressumCRF(new=True, x_file=x_file, y_file=y_file)

def train_product_name(x_file, y_file):
    """Train the product name crf.

    Arguments:
        x_file : file with training sequences
        y_file : file with training labels for the sequences

    """
    clf = product_crf.ProductnameCRF(new=True, x_file=x_file, y_file=y_file)

def test_clf(config, directory, type_):
    """Test the different classifier.

    Arguments:
        directory : path to directory with test data
        type_ : type of the classifier. one of: 'shop', 'food' or 'product'

    """
    # test the classifier and prints a report to stdout
    clf = classifier.Classifier(config, type_ = type_)
    clf.test(directory)

def test_impressum(x_file, y_file):
    """Test the impressum crf.

    Arguments:
        x_file : file with test sequences
        y_file : file with test labels for the sequences

    """
    clf = impressum_crf.ImpressumCRF()
    with codecs.open(x_file, encoding='utf-8') as f:
        x_list = f.readlines()
    with codecs.open(y_file, encoding='utf-8') as f:
        y_list = f.readlines()
    x_list = map(lambda x: x.split(), x_list)
    y_list = map(lambda x: x.split(), y_list)
    clf.test(x_list, y_list)

def test_product_name(x_file, y_file):
    """Test the product name crf.

    Arguments:
        x_file : file with test sequences
        y_file : file with test labels for the sequences

    """
    clf = product_crf.ProductnameCRF()
    x_list, y_list = clf.load_files(x_file, y_file)
    clf.test(x_list, y_list)

def update_clf(config, directory, type_):
    """Update the different classifiers with online learning.

    Arguments:
        directory : path to directory with new training data
        type_ : type of the classifier. one of: 'shop', 'food' or 'product'

    """
    clf = classifier.Classifier(config, type_=type_)
    clf.train_batch_dir(directory)

def test_simple(config, max_tries, delay, delay_module, day, update_rate):
    """Test the general setup of the backend.

    Loads some test jobs into the database and runs every module on
    them to check the general setup.

    Arguments:
        config : dictionary with important configuration information
        max_tries : maximal number of tries for worker
        delay : delay in seconds after no job was found
        delay_module : time in seconds a module is allowed to take
        day : number of times to check the database for jobs per day
        update_rate : number of days after which online learning should
            be performed

    """
    c = db_interface.DBInterface(config)
    cur = c.db.cursor()
    cur.execute("INSERT INTO jobs_to_assign (status_jobs, modules_jobs, path_resources, fk_resources, \
    parent_resources, url_resources, fk_cases) VALUE (%s, %s, %s, %s, %s, %s, %s)",
                (0, '1,2,3,4,5,6,7,8,9,10', "test/top.html",
                 1, None, "foobar.com", 0))
    cur.execute("INSERT INTO jobs_to_assign (status_jobs, modules_jobs, path_resources, screenshot_resources, fk_resources, \
    parent_resources, url_resources, fk_cases) VALUE (%s, %s, %s, %s, %s, %s, %s, %s)",
                (0, '1,2,3,4,5,6,7,8,9,10', "test/imp.html", "test/screenshot_imp.png",
                 2, 1, "foobar.com/imp", 0))
    cur.execute("INSERT INTO jobs_to_assign (status_jobs, modules_jobs, path_resources, fk_resources, \
    parent_resources, url_resources, fk_cases) VALUE (%s, %s, %s, %s, %s, %s, %s)",
                (0, '2,3,4,5,6,7,8,9,10', "test/page1.html",
                 3, 1, "foobar.com/1", 0))
    cur.execute("INSERT INTO jobs_to_assign (status_jobs, modules_jobs, path_resources, fk_resources, \
    parent_resources, url_resources, fk_cases) VALUE (%s, %s, %s, %s, %s, %s, %s)",
                (0, '2,3,4,5,6,7,8,9', "test/page2.html",
                 4, 1, "foobar.com/2", 0))

    c.db.commit()
    c.disconnect()

    # start the worker here!
    w = worker.Worker(config, delay_module)
    w.schedule(max_tries, delay, day, update_rate)

def load_db(config, directory, modules):
    """Load data in database for broder test.

    For every file in the directory a job is submitted to the
    database. The file is registered for every module in
    modules. Other options aren't available. Each file is treated, as
    if it was its own main page.

    Arguments:
        config : directory with important configuration information
        directory : path to directory with test data
        modules : comma separated string with module numbers

    """
    parent = None
    c = db_interface.DBInterface(config)
    cur = c.db.cursor()
    for fk, f in enumerate(os.listdir(directory)):
        f_path = os.path.join(directory, f)
        if not os.path.isfile(f_path):
            continue
        cur.execute("INSERT INTO jobs_to_assign (status_jobs, modules_jobs, path_resources, screenshot_resources, fk_resources, \
        parent_resources, url_resources, fk_cases) VALUE (%s, %s, %s, %s, %s, %s, %s, %s)",
                    (0, modules, f_path, None, fk, parent, "foobar.com", 0))

    c.db.commit()
    c.disconnect()

def read_config(filename):
    """Parse the config file.

    Arguments:
        filename : filename of the config file

    """
    config = dict()
    with open(filename) as f:
        for line in f:
            key, value = line.split("=")
            config[key.strip()] = value.strip()
    return config

def create_parser():
    """Create the argument parser."""
    parser = argparse.ArgumentParser(description = "Backend for the AAPVL-Project.")
    parser.add_argument("--config", type=str, help = "line based configuration file")
    parser.add_argument("--debug", action = "store_true", help = "enable debug-information in log-file")

    subparser = parser.add_subparsers(dest='command')

    train_p = subparser.add_parser("train", help="train different classifier")
    sub_train = train_p.add_subparsers(dest='subcomm')
    train_shop = sub_train.add_parser("shop", help = "train the shop classifier with the data from DIR")
    train_shop.add_argument("dir", type=str, help="directory with data")
    train_food = sub_train.add_parser("food", help = "train the shop classifier with the data from DIR")
    train_food.add_argument("dir", type=str, help="directory with data")
    train_prod = sub_train.add_parser("product", help = "train the product classifier with the data from DIR")
    train_prod.add_argument("dir", type=str, help="directory with data")
    train_imp = sub_train.add_parser("imp", help = "train the crf for address extraction")
    train_imp.add_argument("x", type=str, help="file with addresses or titles of websites respectively in each line")
    train_imp.add_argument("y", type=str, help="file with label sequences in each line corresponding to the tokens in X")
    train_prodn = sub_train.add_parser("prod-name", help = "train the crf for product name extraction")
    train_prodn.add_argument("x", type=str, help="file with addresses or titles of websites respectively in each line")
    train_prodn.add_argument("y", type=str, help="file with label sequences in each line corresponding to the tokens in X")

    test_p = subparser.add_parser("test", help="test different classifier directly and test different functionalities")
    sub_test = test_p.add_subparsers(dest='subcomm')
    test_shop = sub_test.add_parser("shop", help = "test the shop classifier with the data from DIR")
    test_shop.add_argument("dir", type=str, help="directory with data")
    test_food = sub_test.add_parser("food", help = "test the shop classifier with the data from DIR")
    test_food.add_argument("dir", type=str, help="directory with data")
    test_prod = sub_test.add_parser("product", help = "test the product classifier with the data from DIR")
    test_prod.add_argument("dir", type=str, help="directory with data")
    test_imp = sub_test.add_parser("imp", help = "test the crf for address extraction")
    test_imp.add_argument("x", type=str, help="file with addresses or titles of websites respectively in each line")
    test_imp.add_argument("y", type=str, help="file with label sequences in each line corresponding to the tokens in X")
    test_prodn = sub_test.add_parser("prod-name", help = "test the crf for product name extraction")
    test_prodn.add_argument("x", type=str, help="file with addresses or titles of websites respectively in each line")
    test_prodn.add_argument("y", type=str, help="file with label sequences in each line corresponding to the tokens in X")
    sub_test.add_parser("simple", help="use data from ./test to perform a simple check, if all modules can access their data and models")

    update_p = subparser.add_parser("update", help="update the different classifier with new data from a directory")
    sub_update = update_p.add_subparsers(dest='subcomm')
    update_shop = sub_update.add_parser("shop", help = "update the shop classifier with the data from DIR")
    update_shop.add_argument("dir", type=str, help="directory with data")
    update_food = sub_update.add_parser("food", help = "update the shop classifier with the data from DIR")
    update_food.add_argument("dir", type=str, help="directory with data")
    update_prod = sub_update.add_parser("product", help = "update the product classifier with the data from DIR")
    update_prod.add_argument("dir", type=str, help="directory with data")

    load_p = subparser.add_parser("load", help="load data from a directory into database. this can be used for testing")
    load_p.add_argument("dir", type=str, help="path to directory from which all files are added to the database")
    load_p.add_argument("--modules", type=str, help="comma separated list of modules that are added to the database for every file. if omitted, all modules are registered", default="1,2,3,4,5,6,7,8,9,10")

    main_p = subparser.add_parser("run", help="run the backend and process jobs")

    return parser

def main():
    """Main entry point."""
    # parse the arguments
    parser = create_parser()
    args = parser.parse_args()
    # initialize logger
    now = datetime.datetime.now()
    logname = now.strftime('%d_%m_%Y_%H_%M_%S.log')
    if args.debug:
        loglevel = logging.DEBUG
    else:
        loglevel = logging.INFO
    logging.basicConfig(filename=logname,
                        format='%(levelname)s(%(asctime)s):%(filename)s at %(lineno)d:%(message)s',
                        level=loglevel)

    if args.config != None:
        config = read_config(args.config)
    else:
        config = read_config("config/config.default")
    try:
        max_tries = int(config["max_tries"])
    except KeyError:
        sys.stderr.write("the config is in wrong format! can't initialize main component without <max_tries>.")
        sys.exit(1)
    try:
        delay = int(config["delay"])
    except KeyError:
        sys.stderr.write("the config is in wrong format! can't initialize main component without <delay>.")
        sys.exit(1)
    try:
        delay_module = int(config["delay_module"])
    except KeyError:
        sys.stderr.write("the config is in wrong format! can't initialize main component without <delay_module>.")
        sys.exit(1)
    try:
        day = int(config["day"])
    except KeyError:
        sys.stderr.write("the config is in wrong format! can't initialize main component without <day>.")
        sys.exit(1)
    try:
        update_rate = int(config["update_rate"])
    except KeyError:
        sys.stderr.write("the config is in wrong format! can't initialize main component without <update_rate>.")
        sys.exit(1)

    if args.command == 'train':
        if args.subcomm in {'shop', 'food', 'product'}:
            train_clf(config, args.dir, args.subcomm)
        elif args.subcomm == 'imp':
            train_impressum(args.x, args.y)
        elif args.subcomm == 'prod-name':
            train_product_name(args.x, args.y)
        else:
            sys.stderr.write("unknown subcommand {0}\n".format(args.command))
            sys.exit(1)
    elif args.command == 'test':
        if args.subcomm in {'shop', 'food', 'product'}:
            test_clf(config, args.dir, args.subcomm)
        elif args.subcomm == 'imp':
            test_impressum(args.x, args.y)
        elif args.subcomm == 'prod-name':
            test_product_name(args.x, args.y)
        elif args.subcomm == 'simple':
            test_simple(config, max_tries, delay, delay_module, day, update_rate)
        else:
            sys.stderr.write("unknown subcommand {0}\n".format(args.command))
            sys.exit(1)
    elif args.command == 'update':
        if args.subcomm in {'shop', 'food', 'product'}:
            update_clf(config, args.dir, args.subcomm)
        else:
            sys.stderr.write("unknown subcommand {0}\n".format(args.command))
            sys.exit(1)
    elif args.command == 'load':
        load_db(args.dir, args.modules)
    elif args.command == 'run':
        w = worker.Worker(config, delay_module)
        w.schedule(max_tries, delay, day, update_rate)
    else:
        sys.stderr.write("unknown command {0}\n".format(args.command))
        sys.exit(1)

if __name__ == "__main__":
    main()
