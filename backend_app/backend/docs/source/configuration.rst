.. _configuration:

How to configure the program
============================

The program has some parameters (e.g. user name for the database
connection etc.) that should be configured. Therefore a default
configuration file (short: config file) exists and can be
customized. The different parameters and their meaning is explained in
this chapter and the default config file is shown as example. Note:
the keywords are case sensitiv, so make sure you spell them correctly.

Configuration parameters:
-------------------------

host
  IP adress to which the database is reachable. You can leave the
  default value, when you have your database local on your
  machine. default: 127.0.0.1

user
  Username for the database user. default: foo

passwd
  Password for the database user. default: bar

db
  Name of the used database. default: aapvl

max_tries
  Maximum number of failed tries to obtain jobs from the database before
  ending the program. If this number is negative, the program tries four
  times per day to obtain jobs and then sleeps in the background for the
  rest of the time. default: 3

delay
  Time in seconds to sleep before trying to get jobs from the
  database. The delay time is only used, after a try to obtain jobs was
  not succesful and is not used when max_tries is negative (see also
  **max_tries**). default: 3600

delay_module
  Time in seconds after which each module has to finished. If a module
  needs more than delay_module seconds, it is terminated with all its
  subprocesses and None is returned as a result from this
  module. default: 3600

day
  Number of times per day the program should wakeup and try to obtain
  jobs from the database. default: 4

update_rate
  Number of days after which the database should be checked for
  altered classification results for online learning. default: 7

food_vocab  
  Path to a file containing food relevant vocabulary for the food
  classifier (see also chapter :ref:`external_food_vocab`). default:
  data/food_vocab.txt

map_file  
  Path to a file containing an assignment between postal codes and
  german regions. For more details also information on the assumed
  format see chapter :ref:`external_postal_code`. default:
  data/plz.csv
  
legal_numbers
  Path to a file containing all approved boards of control for
  ecological traders. For more information on the file and format see
  also chapter :ref:`external_legal_numbers`. default:
  data/legal_oeko_numbers.txt

health_claim_substances  
  Path to a file with common substances used in health claims. For
  more information on the file, the format and how to update it see
  also chapter :ref:`external_hc_substances` and
  :ref:`substances_online`. default:
  data/health_claim_substances.txt
  
health_claim_diseases
  Path to a file with common diseases used in health claims. For more
  information on the file, the format and how to update it see also
  chapter :ref:`external_hc_disease` and
  :ref:`diseases_online`. default: data/health_claim_diseases.txt
  
health_claim_rejected
  Path to a file with rejected health claims. For more information on
  the file, the format and how to update it see also chapter
  :ref:`external_hc_rejected` and :ref:`rejected_online`. default:
  data/rejected_health_claim.txt

health_claim_declination
  Path to a file with declinations of verbs probably used in health
  claims. For more information on the file, the format and how to
  update it see also chapter :ref:`external_hc_declination` and
  :ref:`declination_online`. default:
  data/health_claim_declination.txt
  
door_list  
  Path to a file with the eu door list. For more information on the
  assumed format see chapter :ref:`external_door`. default:
  data/door_list.csv
  
ingredients_whitelist
  Path to a file with known ingredients. For more information on the
  format or how to update the list see chapter
  :ref:`external_black_white` and :ref:`black_white_online`. default:
  data/whitelist.txt

ingredients_blacklist  
  Path to a file with prohibited ingredients. For more information on
  the format or how to update the list see chapter
  :ref:`external_black_white` and :ref:`black_white_online`. default:
  data/blacklist.txt

parzu
  Path to the parzu executable. See chapter :ref:`installation` for more
  information how to install parzu. default: /home/foo/ParZu/parzu

Example:
--------

The default config file:
::
  host = 127.0.0.1
  user = foo
  passwd = bar
  db = aapvl
  max_tries = 3
  delay = 3600
  delay_module = 3600
  day = 4
  update_rate = 7
  food_vocab = data/food_vocab.txt
  map_file = data/plz.csv
  legal_numbers = data/legal_oeko_numbers.txt
  health_claim_substances = data/health_claim_substances.txt
  health_claim_diseases = data/health_claim_diseases.txt
  health_claim_rejected = data/rejected_health_claim.txt
  health_claim_declination = data/health_claim_declination.txt
  door_list = data/door_list.csv
  ingredients_whitelist = data/whitelist.txt
  ingredients_blacklist = data/blacklist.txt
  parzu = /home/foo/ParZu/parzu
