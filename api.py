import json
import sys
import os

# Suppress stdout to keep the output clean for PHP
sys.stdout = open(os.devnull, 'w')

from utils import load_data, set_up
from scheduler import initial_population, evolutionary_algorithm, simulated_hardening

def main():
    # Read input file path from command line arg
    if len(sys.argv) < 2:
        sys.stderr.write("Error: No input file provided.\n")
        sys.exit(1)
        
    input_file = os.path.abspath(sys.argv[1])
    
    # Change working directory to the script's directory to ensure relative paths work
    os.chdir(os.path.dirname(os.path.abspath(__file__)))
    
    # Initialize data structures
    filled = {}
    subjects_order = {}
    groups_empty_space = {}
    teachers_empty_space = {}

    # Load data
    try:
        data = load_data(input_file, teachers_empty_space, groups_empty_space, subjects_order)
    except Exception as e:
        sys.stderr.write(f"Error loading data: {e}\n")
        sys.exit(1)

    # Setup matrix
    matrix, free = set_up(len(data.classrooms))

    # Run Algorithm
    initial_population(data, matrix, free, filled, groups_empty_space, teachers_empty_space, subjects_order)
    evolutionary_algorithm(matrix, data, free, filled, groups_empty_space, teachers_empty_space, subjects_order)
    simulated_hardening(matrix, data, free, filled, groups_empty_space, teachers_empty_space, subjects_order, os.path.basename(input_file))

    # Restore stdout for final JSON output
    sys.stdout = sys.__stdout__

    # Format Output JSON
    # We need to convert the internal matrix/filled structure into something usable by PHP
    
    # Structure: Day -> Hour -> { Subject, Teacher, Room }
    output_schedule = {}
    
    days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday']
    hours = [9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20]

    for class_index, times in filled.items():
        c = data.classes[class_index]
        
        # Get groups string
        groups_list = []
        for g_name, g_index in data.groups.items():
            if g_index in c.groups:
                groups_list.append(g_name)
        
        # Get classroom name
        # times[0][1] is the column index (classroom index)
        room_index = times[0][1]
        room_name = data.classrooms[room_index].name

        for time_slot in times:
            # time_slot is (row, col)
            row = time_slot[0]
            
            day_index = row // 12
            hour_index = row % 12
            
            if day_index >= len(days): continue # Should not happen
            
            day_name = days[day_index]
            hour_value = hours[hour_index]
            
            if day_name not in output_schedule:
                output_schedule[day_name] = {}
            
            # Format: "09:00 - 10:00"
            time_str = f"{hour_value:02d}:00 - {hour_value+1:02d}:00"
            
            output_schedule[day_name][time_str] = {
                "subject": c.subject,
                "teacher": c.teacher,
                "room": room_name,
                "groups": groups_list,
                "type": c.type
            }

    print(json.dumps(output_schedule, indent=4))

if __name__ == '__main__':
    main()
