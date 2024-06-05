import re

with open('cronistoria2.txt', 'r') as f:
    lines = f.readlines()
    year = 0
    for line in lines:
        if re.match(r'^\*\*\d{4}\*\*$', line):
            year = int(line[2:6])
        if re.match(r'^.\. ', line):
            print(year, line.strip())
            # Write to file
            with open('cronistoriaParsed2.txt', 'a') as f:
                f.write(f'{year}: {line.strip('-').strip()}\n')