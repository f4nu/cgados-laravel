# Read all .txt files, extract the date from the line that starts with "Date: " and rename the file with the date in the format "YYYY-MM-DD_HH-mm-ss.txt"
# The date in the file is in the format "2020-04-10 14:27:00"

import os
import re
import datetime

def extract_date(file_path):
    with open(file_path, 'r') as file:
        for line in file:
            if line.startswith('Date: '):
                date_str = line[6:].strip()
                date = datetime.datetime.strptime(date_str, '%Y-%m-%d %H:%M:%S')
                return date
    return None
    
def rename_file(file_path, date):
    new_file_name = date.strftime('%Y-%m-%d_%H-%M-%S.txt')
    os.rename(file_path, os.path.join(os.path.dirname(file_path), new_file_name))
    
def main():
    for file_name in os.listdir('.'):
        if file_name.endswith('.txt'):
            file_path = os.path.join('', file_name)
            print(file_path)
            date = extract_date(file_path)
            if date:
                rename_file(file_path, date)

if __name__ == '__main__':
    main()
