# Customer Queue Simulator

This application is a queue simulator. It is intended to emulate a customer queue, similar to a grocery store checkout or a phone support line. The simulation runs as a sequence of "iterations". On each iteration, some random number of "customers" will be removed from the queue and then some random number will be added. These random numbers will roughly follow a sinusoidal progression, in order to emulate a natural rise and fall in demand and response. There are two ways to run the simulation, `loop.php` or `ajax.php`.

## Running from the Command Line

Simulations can be run from the command line by executing `php loop.php`. By default, the simulation will run continuously with a 5 second delay between each iteration. The default output looks similar to the following:

    Net Change | Max Wait | Average Wait | Length | Total Processed
    ---------- + -------- + ------------ + ------ + ---------------
            +2 |        0 |       0.0000 |      4 |               0
            +1 |        1 |       1.0000 |      5 |               3
            +4 |        1 |       1.1667 |      9 |               6
    etc...

Each row of the table is a snapshot of the queue data after a single iteration. The columns are:

- `Net Change` &mdash; The net change in customers on the queue
- `Max Wait` &mdash; How long the current oldest customer has been in the queue
- `Average Wait` &mdash; A running average of how long customers wait in the queue
- `Length` &mdash; Current number of customers in the queue
- `Total Processed` &mdash; Total number of customers that have been through the queue.

Passing the flag `-h` or `--help` to `loop.php` will get you the following usage information:

    Queue Simulator

      Usage:
        php loop.php [options]

      Options:
        -h | --help       : Display this help text
        -i | --iterations : Limit queue simulation to this number of iterations
                            Default: unlimited
        -f | --file       : Use this file to load/save queue data
                            Default: queue.dat
        -d | --delay      : Pause this many seconds between iterations. Passing 0
                            causes the queue to run in pure simulation mode,
                            emulating one second delay between iterations without
                            actually pausing.
                            Default: 5
        -c | --csv        : Output status information in CSV format

      Description:
        Queue Simulator emulates a customer queue. On each iteration a random
        number of customers between 1 and 7 will be added to the queue, and some
        number will be removed. The queue uses sine and cosine functions to
        replicate a natural rise and fall in customer demand and response. Queue
        data is cached in a file and will be reloaded at the beginning of execution
        (if it exists). If no iteration limit is given, queue data is saved on each
        iteration, otherwise data is saved at the end of the simulation.

The primary uses of `loop.php` is to experiment with & test the simulator's behavior, and to seed simulation data for `ajax.php`.

## Running via Ajax

A single iteration of the simulation can be run by calling `ajax.php`. A request to this script will load the default simulation data (`queue.dat`), adjust for any skew in request times, run a 1 second iteration, and output a JSON object with data about that iteration. The JSON data looks like:

```json
{
    "net_change": 4,
    "wait": {
        "current_max": 31,
        "average": 59.142371440713
    },
    "length": 100,
    "total_served": 2353
}
```

This is the same data outputted on each row of `loop.php`. Simulations can be run and tracked via a web interface by making an ajax request to `ajax.php` with a 1 second JavaScript interval or timeout.

# Thank You

Any questions, comments, or concerns should be directed to instructors@codeup.com. Thank you.
