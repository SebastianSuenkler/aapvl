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

import MySQLdb
import time
import sys
import logging
import json


class DBInterface:
    """Interface to the used database scheme. Connects with the database
    and performs all the needed queries.

    Attributes:
        db : database connection object
    """
    def __init__(self, config = None):
        """Constructs a new Interface and connects to the database.

        Keyword Arguments:
            config : dictionary which contains important configuration
                information (default: None)

        """
        logging.debug("initializing database-interface")
        self.db = None
        self.connect(config)
        logging.debug("initialized database-interface")

    def connect(self, config = None):
        """Connects to the database.

        Keyword Arguments:
            config : dictionary which contains important configuration
                information (default: None)

        """
        if config == None:
            logging.debug("connecting to database with default-config")
            self.db = MySQLdb.connect(host="127.0.0.1",
                                      user="dorle",
                                      passwd="dorle",
                                      db="new_scheme",
                                      charset="utf8")
        else:
            logging.debug("connecting to database with user config")
            self.db = MySQLdb.connect(host=config["host"],
                                      user=config["user"],
                                      passwd=config["passwd"],
                                      db=config["db"],
                                      charset="utf8")

    def get_job(self):
        """Select one job from the database.

        A query on the database is performed to get a new job with all needed
        information. If no job is found, an empty list, else a list
        with the needed information is returned.
        """
        logging.debug("selecting NOT thread-safe one job from database")
        cur = self.db.cursor()
        # get one job to assign CAUTION! this is not thread-safe!
        cur.execute(("SELECT pk_jobs, modules_jobs, path_resources, \
        screenshot_resources, fk_resources, parent_resources, url_resources, \
        fk_cases FROM jobs_to_assign WHERE status_jobs = 0 LIMIT 1"))

        rows = cur.fetchall()
        if len(rows) != 1:
            # there is no job to assign!
            return []
        row = rows[0]
        cur.execute(("UPDATE jobs_to_assign SET status_jobs = %s \
        WHERE pk_jobs = %s"),
                    (3, row[0]))
        self.db.commit()
        logging.debug("returning results")
        return row

    def update_results(self, input_, results):
        """Update the status and the result for one job.

        Parameters:
            input_ : db information that was returned by :meth:`get_job`.
            results : dictionary containing all results.
        """
        logging.debug("updating the results for a job")
        cur = self.db.cursor()
        # validation_results: 0: keine nachbewertung, 1:
        # nachbewertung, -1: loeschen
        cur.execute(
            ("INSERT INTO results (analysis_results, manual_results, \
            validation_results, path_resources, screenshot_resources, \
            fk_resources, parent_resources, url_resources, fk_cases) \
            VALUE (%s, %s, %s, %s, %s, %s, %s, %s, %s)"),
            (json.dumps(results), json.dumps(results), 0, input_[2], input_[3], input_[4], input_[5], input_[6], input_[7]))
        self.db.commit()
        cur.execute(
            ("UPDATE jobs_to_assign SET status_jobs = %s \
            WHERE pk_jobs = %s"),
            (5, input_[0]))
        logging.debug("updated the database")

    def get_results_with_parent_resources(self, parent_resources):
        """Select the results from a already processed website.

        The stored results for a main page can be retrieved with this
        function. The result contains the keyword "add" where
        additional information for all subpages is stored.

        Parameters:
            parent_resources : fk_resources id for the main page

        """
        logging.debug("selecting results for %s", parent_resources)
        cur = self.db.cursor()

        cur.execute(("SELECT pk_results, analysis_results \
        FROM results WHERE fk_resources = %s"), (parent_resources))

        rows = cur.fetchall()
        if len(rows) != 1:
            logging.warn("could not find parent resource with foreign key %d in results table", parent_resources)
            return []
        row = rows[0]
        logging.debug("returning results")
        return row

    def update_results_with_parent_resources(self, pk_results, results):
        """Update results for a given pk_results id.

        Sets both, analysis_results and manual_results, to the given
        results dictionary.

        Parameters:
            pk_results : pk_results id for a main page
            results : dictionary with all information to be stored

        """
        logging.debug("updating the results for pk: %s", pk_results)
        cur = self.db.cursor()

        cur.execute(
            ("UPDATE results SET analysis_results = %s, manual_results = %s \
            WHERE pk_results = %s"),
            (json.dumps(results), json.dumps(results), pk_results))
        self.db.commit()
        logging.debug("updated the database")

    def set_error_state(self, id_):
        """Set status_jobs to an error-flag for a given pk_jobs.

        For the given id status_jobs is set to 9.

        Parameters:
            id_ : pk_jobs id
        """
        logging.debug("setting error state for job")
        cur = self.db.cursor()
        cur.execute(
            ("UPDATE jobs_to_assign SET status_jobs = %s \
             WHERE pk_jobs = %s"),
            (9, id_))
        self.db.commit()

    def get_manual_data(self):
        """Select all rows where manual results have been altered.

        Only rows, that weren't used for online learning before are
        selected here.

        """
        # TODO: check assumption: validation_results is 1 iff the
        # result is manually checked or edited
        cur = self.db.cursor()
        cur.execute(
            ("SELECT pk_results, path_resources, analysis_results,\
            manual_results FROM results WHERE validation_results = 1 \
            AND updated_results = 0"))
        rows = cur.fetchall()
        return rows

    def update_update_flag(self, rows):
        """Update rows for being used in online-learning.

        For all rows in rows the flag updated_results is set to 1.

        Parameters:
            rows : rows that have been used for online-learning
        """
        cur = self.db.cursor()
        for r in rows:
            cur.execute(
                ("UPDATE results SET updated_results = 1 \
                WHERE pk_results = %s"),
                (r[0]))
        self.db.commit()
    
    def disconnect(self):
        """Close the open connection to the database.
        """
        self.db.close()

