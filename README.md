<h1 align="center">OSX Bluetooth Battery Logger</h1>
<p align="center"><em>Command line tool for logging battery life.</em></p>

<p align="center">
  <a href="LICENSE"><img src="https://img.shields.io/github/license/photogabble/osx-mouse-battery-monitor.svg" alt="License"></a>
</p>

## Usage

The `collect` command is used to parse the output of `system_profiler -xml SPBluetoothDataType` and as a csv line to the file provided by `--output_path`. I use `run.sh` to execute the `collect` command every 60 seconds.

```ps
$ ./bin/mousebattery help collect
Description:
  Parses input from stdin

Usage:
  collect [options]

Options:
  -o, --output_path[=OUTPUT_PATH]  Path you want to output csv lines to.
  -p, --progress                   Display progress bar
  -a, --BD_ADDR=BD_ADDR            Bluetooth address for mouse you want to monitor
  -h, --help                       Display this help message
  -q, --quiet                      Do not output any message
  -V, --version                    Display this application version
      --ansi                       Force ANSI output
      --no-ansi                    Disable ANSI output
  -n, --no-interaction             Do not ask any interactive question
  -v|vv|vvv, --verbose             Increase the verbosity of messages: 1 for normal output, 2 for more verbose output and 3 for debug
```

## Example Output

```ps
1555501362   6% [=|..........................] 
```
