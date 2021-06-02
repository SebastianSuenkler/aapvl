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

import time
import codecs
from postal.expand import expand_address
import db_interface
import classifier
import preprocess
import sys
import impressum
import logging
import signal
import information_extractor
import os
import json
import oeko
import health_claims
import geoschutz_check
import ingredient_extractor
import bioc
import my_exceptions

# handler for too long executions of modules in worker
def handler(signum, frame):
    """Handle too long executions of modules.

    Arguments:
        signum : signal number, not used
        frame : not used

    """
    logging.error("module takes too long. killing now")
    raise my_exceptions.TooLongException("killing module")

class Worker:
    """Worker combines all the modules to get the jobs and process them.

    Attributes:
        _delay_module : time after that a module should be stopped
        _interface : interface to the database
        _lm_theta : probability threshold for the lm-classifier
        _shop_theta : probability threshold for the shop-classifier
        _product_theta : probability threshold for the product-classifier
        _shop_clf : shop classifier
        _food_clf : food classifier
        _product_clf : product classifier
        _p : preprocessor for websites
        _imp : impressums handler
        _extractor : information extractor
        oeko : oeko module
        health : health claim module
        geo : geoschutz module
        ingr : ingredients module
        bioc : bioc module

    """
    def __init__(self, config, delay_module = 180):
        """Initialize all the needed modules.

        Arguments:
            config : dictionary with connection details for db_interface
                and modules

        Keyword Arguments:
            delay_module : time in seconds, after which each module is
                killed

        """
        logging.debug("initializing worker")
        self._delay_module = delay_module
        self._interface = db_interface.DBInterface(config)
        # TODO: these thresholds should be set right
        self._lm_theta = 40
        self._shop_theta = 50
        self._product_theta = 50
        # load classifier
        self._shop_clf = classifier.Classifier(config, type_ = 'shop')
        self._food_clf = classifier.Classifier(config, type_ = 'food')
        self._product_clf = classifier.Classifier(config, type_ = 'product')
        self._p = preprocess.Preprocessor()
        self._imp = impressum.Impressum_handler(config)
        self._extractor = information_extractor.InformationExtractor()
        self.oeko = oeko.Oeko(config)
        self.health = health_claims.HealthClaims(config)
        self.geo = geoschutz_check.Geoschutz(config)
        self.ingr = ingredient_extractor.IngredientExtractor(config)
        self.bioc = bioc.BiocStore()
        # initialize signal
        signal.signal(signal.SIGALRM, handler)
        logging.debug("initialized worker")
        
    def process_job(self, input_file, input_image, jobs_str):
        """Process a job and return the results of the single modules.
        
        Arguments:
            input_file : path to file that should be processed.
                can be an url
            input_image : path to a screenshot of the file
            jobs_str : comma separated string with list of modules
                that should be used

        """
        # look at job_schedule
        results = dict()
        logging.debug("processing job with %s, %s", input_file, jobs_str)
        jobs = jobs_str.split(',')
        jobs = map(lambda x: x.strip(), jobs)
        jobs.sort()
        text_links, title = self._p.preprocess_file(input_file)
        text, _ = self._p.preprocess_file(input_file, links=False)
        imp = None
        for job in jobs:
            if job == '1':
                logging.info("handling impressum")
                # handle impressum
                signal.alarm(self._delay_module)
                try:
                    results['1'] = self._imp.process_text(text)
                except my_exceptions.TooLongException, exp:
                    results['1'] = None
                    logging.error("impressum")
                    sys.stderr.write("impressum: {0}\n".format(exp))
                signal.alarm(0)
            elif job == '2':
                logging.info("handling shop")
                # get the website or read the file
                signal.alarm(self._delay_module)
                try:
                    pred = self._shop_clf.predict_prob([text_links])
                    results['2'] = pred[0] # there is exactly one label in pred
                except my_exceptions.TooLongException, exp:
                    results['2'] = None
                    logging.error("shop")
                    sys.stderr.write("shop: {0}\n".format(exp))
                signal.alarm(0)
            elif job == '3':
                logging.info("handling food")
                # get the website or read the file
                signal.alarm(self._delay_module)
                try:
                    pred = self._food_clf.predict_prob([text_links])
                    results['3'] = pred[0] # there is exactly one label in pred
                except my_exceptions.TooLongException, exp:
                    results['3'] = None
                    logging.error("food")
                    sys.stderr.write("food: {0}\n".format(exp))
                signal.alarm(0)
            elif job == '4':
                logging.info("handling product")
                # get the website or read the file
                signal.alarm(self._delay_module)
                try:
                    pred = self._product_clf.predict_prob([text_links])
                    results['4'] = pred[0] # there is exactly one label in pred
                except my_exceptions.TooLongException, exp:
                    results['4'] = None
                    logging.error("product")
                    sys.stderr.write("product: {0}\n".format(exp))
                signal.alarm(0)
            elif job == '5':
                logging.info("extracting information")
                signal.alarm(self._delay_module)
                try:
                    results['5'] = dict()
                    name = self._extractor.extract_productname(title)
                    number = self._extractor.extract_artikelnummer(text)
                    results['5']['name'] = name
                    results['5']['number'] = number
                    logging.debug(u"extracted information: {0}, {1}".format(name, number))
                except my_exceptions.TooLongException, exp:
                    results['5'] = None
                    logging.error("extracting information")
                    sys.stderr.write("extracting information: {0}\n".format(exp))
                signal.alarm(0)
            elif job == '6':
                logging.info("handling oeko stuff")
                signal.alarm(self._delay_module)
                try:
                    results['6'] = self.oeko.check_text(text)
                    if not input_image is None:
                        results['6'].update(self.oeko.check_image(input_image))
                except my_exceptions.TooLongException, exp:
                    results['6'] = None
                    logging.error("handling oeko stuff")
                signal.alarm(0)
            elif job == '7':
                logging.info("handling health claims")
                signal.alarm(self._delay_module)
                try:
                    results['7'] = dict()
                    results['7']['1'] = dict()
                    logging.debug("handling substance check")
                    sub, dis = self.health.check_disease_substances(text)
                    results['7']['1']['substances'] = sub
                    results['7']['1']['diseases'] = dis
                    logging.debug("handling fix patterns")
                    results['7']['2'] = self.health.check_fix_patterns(text)
                except my_exceptions.TooLongException, exp:
                    results['7']['1'] = None
                    results['7']['2'] = None
                    logging.error("handling simple health claims")
                else:
                    try:
                        logging.debug("handling semantic relations")
                        results['7']['3'] = self.health.check_semantic_relations(text)
                    except my_exceptions.TooLongException, exp:
                        logging.error("handling semantic relations")
                signal.alarm(0)
            elif job == '8':
                logging.info("handling geoschutz")
                signal.alarm(self._delay_module)
                try:
                    try:
                        name = results['5']['name']
                        results['8'] = self.geo.search(name)
                    except KeyError:
                        results['8'] = "no name found"
                except my_exceptions.TooLongException, exp:
                    results['8'] = None
                    logging.error("handling geoschutz")
                signal.alarm(0)
            elif job == '9':
                results['9'] = dict()
                logging.info("extracting ingredients list")
                signal.alarm(self._delay_module)
                try:
                    ingredients = self.ingr.extract(text)
                    results['9']['all'] = ingredients
                    results['9']['fishy'] = self.ingr.whitelist_check(ingredients)
                except my_exceptions.TooLongException, exp:
                    results['9'] = None
                    logging.error("extracting ingredients list")
                    sys.stderr.write("extracting ingredients list: {0}\n".format(exp))
                signal.alarm(0)
            elif job == '10':
                logging.info("handling bioc")
                signal.alarm(self._delay_module)
                try:
                    try:
                        adrs = results['1']
                        parts = []
                        for a in adrs:
                            parts.append([a[0]["Strasse"],
                                          a[0]["PLZ"],
                                          a[0]["Ort"]])
                            parts.append([a[0]["Strasse"],
                                          "DE",
                                          a[0]["PLZ"],
                                          a[0]["Ort"]])
                            if (a[0]["PLZ"] is not None
                                and a[0]["PLZ"].startswith("D-")):
                                parts.append([a[0]["Strasse"],
                                              a[0]["PLZ"][2:],
                                              a[0]["Ort"]])
                        logging.debug("parts: {0}".format(parts))
                        parts = filter(lambda x: not None in x, parts)
                        logging.debug("clean parts: {0}".format(parts))
                        parts = map(lambda x: " ".join(x), parts)
                        logging.debug("joined parts: {0}".format(parts))
                        to_check = reduce(lambda x, y: x.__add__(expand_address(y)), parts, [])
                        logging.debug("to_check: {0}".format(to_check))
                        results['10'] = self.bioc.get_certificate_for_addresses(to_check)
                    except KeyError:
                        results['10'] = "no complete address available"
                except my_exceptions.TooLongException, exp:
                    results['10'] = None
                    logging.error("handling bioc")
                signal.alarm(0)
            else:
                logging.warn("unknown job %s", job)
        logging.debug("done with job")
        return results

    def _equal(self, imp1, imp2):
        """Compare two addresses, if they are equal.

        Arguments:
            imp1 : dictionary with address information
            imp2 : dictionary with address information

        """
        return (imp1["Unternehmen"] == imp2["Unternehmen"] and
                imp1["Strasse"] == imp2["Strasse"] and
                imp1["PLZ"] == imp2["PLZ"] and
                imp1["Ort"] == imp2["Ort"] and
                imp1["Land"] == imp2["Land"] and
                imp1["Bundesland"] == imp2["Bundesland"] and
                imp1["Kreis"] == imp2["Kreis"])

    def _get_impressum(self, top, imp):
        """Chose at most two addresses from a list.

        Of a list of different addresses from the main page and the
        impressum page two are choosen according to the following
        heuristic: if there are only addresses from the main page,
        return the first two; if there are addresse on both pages,
        return the first address from the impressum page and all
        addresses that are on both pages or the second address from
        the impressum page.

        Arguments:
            top : list of addresses from the main page
            imp : list of addresses from the impressum page

        """
        if len(imp) == 0:
            if len(top) >= 2:
                return top[:2]
            return top
        result = []
        result.append(imp[0])
        for imp1 in top:
            for imp2 in imp:
                if self._equal(imp1, imp2):
                    result.append(imp1)
                    break
            if len(result) > 1:
                break

        if len(result) == 1 and len(imp) > 1:
            result.append(imp[1])
        return result

    def _cum_avg(self, prev, n, x):
        """Calculate the moving average.

        Arguments:
            prev : previous average
            n : number of samples
            x : next value

        """
        if x == None:
            return prev
        return float(x + n*prev)/float(n+1)

    def _update_top_level_domain(self, input_, results):
        """Update additional information for the main page.

        Load the last additional information from the database for the
        current main page and add more results to it. After that write
        the changes to the database.

        Arguments:
            input_ : row from database that was obtained with
                :meth:`get_job_and_process`
            results : dictionary with results for the file represented
                through input_

        """
        logging.debug("update for toplevel domain %s with site %s", input_[5], input_[4])
        parent_resources = input_[5]
        if parent_resources == None:
            parent_resources = input_[4]
            logging.debug("fk_resources for parent: %s", parent_resources)
        logging.debug("getting old results")
        row = self._interface.get_results_with_parent_resources(parent_resources)
        if len(row) > 0:
            logging.debug("calculating new results")
            top_results = json.loads(row[1])
            # change the results from the toplevel domain
            top_results["add"]["total_sites"] += 1
            try:
                value = results["2"]
                top_results["add"]["shop_avg"] = self._cum_avg(top_results["add"]["shop_avg"],
                                                               top_results["add"]["shop_sites"], value)
                top_results["add"]["shop_sites"] += 1
                if value is not None and value > self._shop_theta:
                    top_results["add"]["shop_one"] = True
            except KeyError:
                pass
            try:
                value = results["3"]
                top_results["add"]["lm_avg"] = self._cum_avg(top_results["add"]["lm_avg"],
                                                             top_results["add"]["lm_sites"], value)
                top_results["add"]["lm_sites"] += 1
                if value is not None and value > self._lm_theta:
                    top_results["add"]["lm_one"] = True
            except KeyError:
                pass
            try:
                value = results["6"]
                ads_set = set(top_results["add"]["oeko"]["ads"])
                ads_set.update(value["ads"])
                top_results["add"]["oeko"]["ads"] = list(ads_set)
                legal_set = set(top_results["add"]["oeko"]["legal"])
                legal_set.update(value["legal"])
                top_results["add"]["oeko"]["legal"] = list(legal_set)
                fake_set = set(top_results["add"]["oeko"]["fake"])
                fake_set.update(value["fake"])
                top_results["add"]["oeko"]["fake"] = list(fake_set)
                try:
                    top_results["add"]["oeko"]["logos"].append(value["logos"])
                except KeyError:
                    pass
            except KeyError:
                pass
            try:
                certs = results["10"]
                if certs is not None and len(certs) > 0:
                    top_results["add"]["bioc"].append(certs)
            except KeyError:
                pass
            logging.debug("writing new results to database")
            self._interface.update_results_with_parent_resources(row[0], top_results)
            logging.debug("finished writing new results")
        else:
            logging.warn("could not update parent resource for job %s", input_[0])

    def get_job_and_process(self):
        """Get a job from database, calculate results, update database."""
        logging.debug("get job from database and process")
        ret = False
        try:
            row = self._interface.get_job()
            if len(row) != 0:
                ret = True
                logging.info("processing job %s", row[0])
                path_resources = row[2]
                res = dict()
                auto = self.process_job(path_resources, row[3], row[1])
                res["modules"] = auto
                # check if site is toplevel domain
                if row[5] == None:
                    res["add"] = dict()
                    res["add"]["total_sites"] = 0
                    res["add"]["shop_sites"] = 0
                    res["add"]["lm_sites"] = 0
                    res["add"]["shop_avg"] = 0
                    res["add"]["lm_avg"] = 0
                    res["add"]["shop_one"] = False
                    res["add"]["lm_one"] = False
                    res["add"]["oeko"] = {"ads":[], "fake":[], "legal":[], "logos":[]}
                    res["add"]["bioc"] = []
                    # TODO: maybe update the values before they are
                    # written to the database!

                # get the results and update the tables!
                self._interface.update_results(row, res)
                # get the results for the toplevel-domain and update average and one-hot
                self._update_top_level_domain(row, res["modules"])
            else:
                logging.debug("found no job")
        except (ImportError, KeyboardInterrupt):
            raise
        except:
            self._interface.set_error_state(row[0])
            logging.error("error while processing job {0}: {1}".format(row[0], sys.exc_info()[1]))

        return ret

    def online_training_clfs(self):
        """Online train the classifiers.

        From the database all data, that was manually evaluated and
        not yet used for training is selected. With a filter only
        changed values are used to train the three classifiers. The
        classifiers are then updated with online learning and all used
        data is marked as such.

        """
        rows = self._interface.get_manual_data()
        if len(rows) == 0:
            logging.info("no manually labeled data to retrain from")
        else:
            logging.info('{0} rows for online learning'.format(len(rows)))
            data = []
            label_shop = []
            label_food = []
            label_product = []
            for _, filename, a_res, m_res in rows:
                with open(filename, 'rb') as f:
                    text = f.read()
                    text.decode("utf-8", 'strict')
                    data.append(text)
                automatic = json.loads(a_res)
                manual = json.loads(m_res)
                m = manual['modules']
                a = automatic['modules']
                try:
                    if m['2'] != a['2']:
                        if m['2'] in {0,1}:
                            s = int(not m['2'])
                        else:
                            s = int(m['2'] < self._shop_theta)
                    else:
                        s = -1
                except KeyError:
                    s = -1
                try:
                    if m['3'] != a['3']:
                        if m['3'] in {0,1}:
                            lm = int(not m['3'])
                        else:
                            lm = int(m['3'] < self._lm_theta)
                    else:
                        lm = -1
                except KeyError:
                    lm = -1
                try:
                    if m['4'] != a['4']:
                        if m['4'] in {0,1}:
                            p = int(not m['4'])
                        else:
                            p = int(m['4'] < self._product_theta)
                    else:
                        p = -1
                except KeyError:
                    p = -1
                label_shop.append(s)
                label_food.append(lm)
                label_product.append(p)
            logging.info('updating classifiers')
            # do batch learning for every classifier
            tmp = filter(lambda x: x[1] != -1, zip(data, label_shop))
            if len(tmp) > 0:
                data_shop, label_shop = zip(*tmp)
                self._shop_clf.train_batch(data_shop, label_shop)
            tmp = filter(lambda x: x[1] != -1, zip(data, label_food))
            if len(tmp) > 0:
                data_food, label_food = zip(*tmp)
                self._food_clf.train_batch(data_food, label_food)
            tmp = filter(lambda x: x[1] != -1, zip(data, label_product))
            if len(tmp) > 0:
                data_product, label_product = zip(*tmp)
                self._product_clf.train_batch(data_product, label_product)

            logging.info('updating updated_flag status for used rows')
            # set for the rows in db 'online' to 1
            self._interface.update_update_flag(rows)

    def shutdown(self):
        """Shut down the worker and close connection to database."""
        logging.debug("shutting down")
        self._interface.disconnect()
        logging.debug("shutted down")
    
    def schedule(self, max_tries, delay, day, update_rate):
        """Register a worker for processing jobs.

        :meth:`get_job_and_process` is called periodically to process
        all available jobs. If there is no job found, the worker
        sleeps for a given amount of time before trying to get a job
        again. Either after a specific number of tries the worker is
        shut down or the worker tries always to get a new job a
        specific number of times a day. At a given intervall the
        database is checked for manually evaluated data that is newly
        available.

        Arguments:
            max_tries : the maximal number of tries, the worker should
                check for new jobs. if -1, the worker won't stop checking
            delay : the time in seconds the worker should sleep between
                checking for jobs
            day : number of times the worker should check for new jobs per
                day. only used with max_tries = -1
            update_rate : number of days the worker should wait before
                checking for newly available manually evaluated data

        """
        logging.debug("started schedule with %d tries, %d delay and %d checks per day", max_tries, delay, day)
        days_since_update = 0
        if max_tries < 0:
            delay = (60 * 60 * 24) / max(day, 1)
            while True:
                if not self.get_job_and_process():
                    if days_since_update >= update_rate:
                        self.online_training_clfs()
                        days_since_update = 0
                    days_since_update += 1/float(day)
                    time.sleep(delay)
        else:
            tries = 0
            while tries < max_tries:
                if not self.get_job_and_process():
                    tries += 1
                    time.sleep(delay)
            logging.debug("reached %d tries. had %d max_tries", tries, max_tries)
            self.shutdown()
