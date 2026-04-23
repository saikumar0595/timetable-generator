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

    if len(data.classes) == 0:
        sys.stdout = sys.__stdout__
        print(json.dumps({
            "schedule": {},
            "statistics": {
                "hard_constraints": 100.0,
                "soft_constraints": 100.0,
                "total_idle_groups": 0,
                "max_idle_group_day": 0,
                "avg_idle_groups": 0.0,
                "total_idle_teachers": 0,
                "max_idle_teacher_day": 0,
                "avg_idle_teachers": 0.0,
                "free_hour_exists": "Yes"
            }
        }, indent=4))
        sys.exit(0)

    # Run Algorithm
    initial_population(data, matrix, free, filled, groups_empty_space, teachers_empty_space, subjects_order)
    evolutionary_algorithm(matrix, data, free, filled, groups_empty_space, teachers_empty_space, subjects_order)
    simulated_hardening(matrix, data, free, filled, groups_empty_space, teachers_empty_space, subjects_order, os.path.basename(input_file))

    # Restore stdout for final JSON output
    sys.stdout = sys.__stdout__

    # Calculate Statistics
    from costs import check_hard_constraints, subjects_order_cost, empty_space_groups_cost, empty_space_teachers_cost, free_hour
    
    cost_hard = check_hard_constraints(matrix, data)
    hard_satisfied = 100.0 if cost_hard == 0 else (1.0 / (1.0 + cost_hard)) * 100
    soft_satisfied = subjects_order_cost(subjects_order)
    
    empty_groups, max_empty_group, average_empty_groups = empty_space_groups_cost(groups_empty_space)
    empty_teachers, max_empty_teacher, average_empty_teachers = empty_space_teachers_cost(teachers_empty_space)
    f_hour = free_hour(matrix)

    # Format Output JSON
    output_data = {
        "schedule": {},
        "statistics": {
            "hard_constraints": round(hard_satisfied, 2),
            "soft_constraints": round(soft_satisfied, 2),
            "total_idle_groups": empty_groups,
            "max_idle_group_day": max_empty_group,
            "avg_idle_groups": round(average_empty_groups, 2),
            "total_idle_teachers": empty_teachers,
            "max_idle_teacher_day": max_empty_teacher,
            "avg_idle_teachers": round(average_empty_teachers, 2),
            "free_hour_exists": "Yes" if f_hour != -1 else "No"
        }
    }
    
    output_schedule = output_data["schedule"]
    
    days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday']
    # 8 Periods Configuration
    period_times = [
        "09:30 - 10:20", # Period 1
        "10:20 - 11:10", # Period 2
        "11:10 - 12:00", # Period 3
        "12:00 - 12:50", # Period 4
        "01:30 - 02:15", # Period 5 (Post Lunch)
        "02:15 - 03:00", # Period 6
        "03:00 - 03:45", # Period 7
        "03:45 - 04:30"  # Period 8
    ]

    for class_index, times in filled.items():
        c = data.classes[class_index]
        
        # Get groups string
        groups_list = []
        for g_name, g_index in data.groups.items():
            if g_index in c.groups:
                groups_list.append(g_name)
        
        # Get classroom name
        room_index = times[0][1]
        room_name = data.classrooms[room_index].name

        for time_slot in times:
            # time_slot is (row, col)
            row = time_slot[0]
            
            day_index = row // 8  # 8 periods per day
            period_index = row % 8
            
            if day_index >= len(days): continue 
            
            day_name = days[day_index]
            if period_index >= len(period_times): continue

            time_str = period_times[period_index]
            
            if day_name not in output_schedule:
                output_schedule[day_name] = {}
            
            if time_str not in output_schedule[day_name]:
                output_schedule[day_name][time_str] = []
            
            output_schedule[day_name][time_str].append({
                "subject": c.subject,
                "teacher": c.teacher,
                "room": room_name,
                "groups": groups_list,
                "type": c.type
            })

    print(json.dumps(output_data, indent=4))

if __name__ == '__main__':
    main()
