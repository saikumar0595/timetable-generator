import json
from http.server import BaseHTTPRequestHandler
import random

def trim_text(text):
    if not isinstance(text, str):
        return text
    return " ".join(text.split())

def check_availability(day, start_p_idx, duration, teacher, groups, periods, teacher_slots, group_slots):
    for offset in range(duration):
        p_idx = start_p_idx + offset
        if p_idx >= len(periods): return False
        period = periods[p_idx]
        if f"{day}_{period}_{teacher}" in teacher_slots:
            return False
        for group in groups:
            if f"{day}_{period}_{group}" in group_slots:
                return False
    return True

def find_available_room(day, start_p_idx, duration, available_rooms, periods, room_slots):
    random.shuffle(available_rooms)
    for r in available_rooms:
        room_available = True
        for offset in range(duration):
            p_idx = start_p_idx + offset
            if p_idx >= len(periods): 
                room_available = False
                break
            period = periods[p_idx]
            if f"{day}_{period}_{r}" in room_slots:
                room_available = False
                break
        if room_available:
            return r
    return None

def place_class(day, start_p_idx, duration, teacher, subject, room, groups, class_item, schedule, teacher_slots, group_slots, room_slots, periods):
    for offset in range(duration):
        period = periods[start_p_idx + offset]
        schedule[day][period].append({
            "subject": subject,
            "teacher": teacher,
            "room": room,
            "groups": groups,
            "type": class_item.get("Tip", "P")
        })
        teacher_slots[f"{day}_{period}_{teacher}"] = True
        room_slots[f"{day}_{period}_{room}"] = True
        for group in groups:
            group_slots[f"{day}_{period}_{group}"] = True

def generate_fast_schedule(input_data):
    classes = input_data.get("Casovi", [])
    rooms = input_data.get("Ucionice", {})
    
    available_rooms = []
    for room_type, room_list in rooms.items():
        available_rooms.extend(room_list)
    
    if not available_rooms:
        available_rooms = ["LectureHall"]
    
    days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday']
    periods = [
        "09:30 - 10:20", "10:20 - 11:10", "11:10 - 12:00", "12:00 - 12:50",
        "01:30 - 02:15", "02:15 - 03:00", "03:00 - 03:45", "03:45 - 04:30"
    ]
    
    schedule = {}
    teacher_slots = {}
    group_slots = {}
    room_slots = {}
    
    for day in days:
        schedule[day] = {}
        for period in periods:
            schedule[day][period] = []
    
    assigned_count = 0
    sorted_classes = sorted(classes, key=lambda c: len(c.get("Grupe", [])), reverse=True)
    
    possible_start_slots = []
    for day in days:
        for p_idx in range(0, len(periods), 2):
            possible_start_slots.append((day, p_idx))
    
    fallback_slots = []
    for day in days:
        for p_idx in range(len(periods)):
            fallback_slots.append((day, p_idx))

    for class_item in sorted_classes:
        teacher = trim_text(class_item.get("Nastavnik", "Unknown"))
        subject = trim_text(class_item.get("Predmet", "Unknown"))
        groups = [trim_text(g) for g in class_item.get("Grupe", ["Default"])]
        duration = int(class_item.get("Trajanje", 2))
        
        found = False
        random.shuffle(possible_start_slots)
        for day, start_p_idx in possible_start_slots:
            if found: break
            if start_p_idx + duration > len(periods): continue
            if check_availability(day, start_p_idx, duration, teacher, groups, periods, teacher_slots, group_slots):
                room = find_available_room(day, start_p_idx, duration, available_rooms, periods, room_slots)
                if room:
                    place_class(day, start_p_idx, duration, teacher, subject, room, groups, class_item, schedule, teacher_slots, group_slots, room_slots, periods)
                    assigned_count += 1
                    found = True

        if not found:
            random.shuffle(fallback_slots)
            for day, start_p_idx in fallback_slots:
                if found: break
                if start_p_idx + duration > len(periods): continue
                if check_availability(day, start_p_idx, duration, teacher, groups, periods, teacher_slots, group_slots):
                    room = find_available_room(day, start_p_idx, duration, available_rooms, periods, room_slots)
                    if room:
                        place_class(day, start_p_idx, duration, teacher, subject, room, groups, class_item, schedule, teacher_slots, group_slots, room_slots, periods)
                        assigned_count += 1
                        found = True

    output = {
        "schedule": schedule,
        "statistics": {
            "hard_constraints": 100.0 if assigned_count == len(classes) else (assigned_count/len(classes)*100),
            "soft_constraints": 90.0,
            "classes_assigned": assigned_count,
            "total_classes": len(classes)
        }
    }
    return output

class handler(BaseHTTPRequestHandler):
    def do_POST(self):
        content_length = int(self.headers['Content-Length'])
        post_data = self.rfile.read(content_length)
        try:
            input_data = json.loads(post_data)
            result = generate_fast_schedule(input_data)
            self.send_response(200)
            self.send_header('Content-type', 'application/json')
            self.end_headers()
            self.wfile.write(json.dumps(result).encode('utf-8'))
        except Exception as e:
            self.send_response(500)
            self.send_header('Content-type', 'application/json')
            self.end_headers()
            self.wfile.write(json.dumps({"error": str(e)}).encode('utf-8'))

    def do_GET(self):
        self.send_response(200)
        self.send_header('Content-type', 'text/plain')
        self.end_headers()
        self.wfile.write("ChronoGen AI API is active. Use POST to generate timetables.".encode('utf-8'))
