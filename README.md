# KeyManager
Simple PHP class to create a simple secure key manager to allow downloading or displaying of keys.

The output is always plain text (o a stream for download).

Feel free to use, fork, share, etc. Create pull requests or issues if needed :)

## Configuration
Edit config/config.json (it's on a separated dir for security).

### Options

* show_help (true/false): help message when wrong parameter or value is input.
* show_keys (true/false): show valid key values when showing help.
* default_first_key (true/false): if no key selected, then show the first key.

### Default
Provided by this repo:

    {
        "options": {
            "show_help": true,
            "show_keys": false,
            "default_first_key": true
        },
        "keys": {
        }
    }

### Internal default
Config file is mandatory, and an error is shown if missing. However, the whole *options* part is not, and can be missing. Or any key of it could be missing w/o issue. The internal default is always **false** for every option.

## License
KeyManager by [HacKan](https://twitter.com/hackancuba) GNU GPL v3.0 or newer.