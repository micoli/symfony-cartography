#!/usr/bin/env python3
import re
import subprocess


def run_command(cmd):
    return subprocess.run(['bash', '-c', cmd], capture_output=True, check=True).stdout.decode("utf-8").strip("\n")

def get_file_contents(filename):
    with open(filename, 'r', encoding='utf-8') as file_handler:
        return file_handler.read()


output = ''
with open('README.md', 'r', encoding='utf-8') as file_handler:
    is_between_command_placeholders = False
    is_between_include_placeholders = False
    for line in file_handler:

        matches = re.match(r'\[\/\/\]\: \<\> \(command-placeholder-start "(.*)"\)', line)
        if not is_between_command_placeholders and matches is not None:
            is_between_command_placeholders = True
            output += line
            output += "```\n%s\n```\n" % (run_command(matches.group(1)))
            continue

        matches = re.match(r'\[\/\/\]\: \<\> \(command-placeholder-end\)', line)
        if is_between_command_placeholders and matches is not None:
            is_between_command_placeholders = False
            output += line
            continue

        matches = re.match(r'\[\/\/\]\: \<\> \(include-placeholder-start "(.*)"\)', line)
        if not is_between_include_placeholders and matches is not None:
            is_between_include_placeholders = True
            output += line
            output += "```\n%s\n```\n" % (get_file_contents(matches.group(1)))
            continue

        matches = re.match(r'\[\/\/\]\: \<\> \(include-placeholder-end\)', line)
        if is_between_include_placeholders and matches is not None:
            is_between_include_placeholders = False
            output += line
            continue
        if not is_between_command_placeholders and not is_between_include_placeholders:
            output += line

with open('README.md', 'w', encoding='utf-8') as file_handler:
    file_handler.write(output)
