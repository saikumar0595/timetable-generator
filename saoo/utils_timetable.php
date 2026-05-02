<?php
/**
 * ChronoGen Timetable Utility Functions
 * Handles workload calculation, conflict detection, and heatmap data generation
 */

/**
 * Calculates workload metrics for all teachers or a specific teacher
 * @param array $timetable The full timetable object
 * @param int|null $teacher_id Specific teacher ID to filter by (optional)
 * @return array Workload data
 */
function calculateTeacherWorkload($timetable, $teacher_id = null) {
    $workload = [];
    $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
    
    foreach ($timetable as $day => $periods) {
        foreach ($periods as $period => $sessions) {
            foreach ($sessions as $session) {
                $teacher = $session['teacher'];
                
                if ($teacher_id && $teacher != $teacher_id) continue;
                
                if (!isset($workload[$teacher])) {
                    $workload[$teacher] = [
                        'total_hours' => 0,
                        'days' => array_fill_keys($days, 0),
                        'sessions_count' => 0,
                        'back_to_back_max' => 0,
                        'conflicts' => 0
                    ];
                }
                
                // Assuming each period is ~1 hour for simple calculation
                $workload[$teacher]['total_hours'] += 1;
                $workload[$teacher]['days'][$day] += 1;
                $workload[$teacher]['sessions_count'] += 1;
            }
        }
    }
    
    // Calculate alerts and levels
    foreach ($workload as $teacher => &$data) {
        $max_daily = max($data['days']);
        $data['level'] = 'Normal';
        $data['color'] = '#10b981'; // Green
        
        if ($max_daily > 8) {
            $data['level'] = 'Critical';
            $data['color'] = '#ef4444'; // Red
        } elseif ($max_daily > 6) {
            $data['level'] = 'Heavy';
            $data['color'] = '#0ea5e9'; // Blue
        } elseif ($max_daily > 4) {
            $data['level'] = 'Moderate';
            $data['color'] = '#3b82f6'; // Light Blue
        }
    }
    
    return $workload;
}

/**
 * Detects scheduling conflicts (double bookings, etc.)
 * @param array $timetable The full timetable object
 * @return array List of detected conflicts
 */
function detectScheduleConflicts($timetable) {
    $conflicts = [];
    
    foreach ($timetable as $day => $periods) {
        foreach ($periods as $period => $sessions) {
            // Check Room Double Booking
            $rooms = [];
            // Check Teacher Double Booking
            $teachers = [];
            // Check Group Double Booking
            $groups = [];
            
            foreach ($sessions as $session) {
                $room = $session['room'];
                $teacher = $session['teacher'];
                $session_groups = $session['groups'];
                
                if (isset($rooms[$room])) {
                    $conflicts[] = [
                        'type' => 'Room Conflict',
                        'day' => $day,
                        'period' => $period,
                        'resource' => $room,
                        'message' => "Room $room is double booked"
                    ];
                }
                $rooms[$room] = true;
                
                if (isset($teachers[$teacher])) {
                    $conflicts[] = [
                        'type' => 'Teacher Conflict',
                        'day' => $day,
                        'period' => $period,
                        'resource' => $teacher,
                        'message' => "Teacher $teacher has overlapping sessions"
                    ];
                }
                $teachers[$teacher] = true;
                
                foreach ($session_groups as $group) {
                    if (isset($groups[$group])) {
                        $conflicts[] = [
                            'type' => 'Group Conflict',
                            'day' => $day,
                            'period' => $period,
                            'resource' => $group,
                            'message' => "Group $group has overlapping sessions"
                        ];
                    }
                    $groups[$group] = true;
                }
            }
        }
    }
    
    return $conflicts;
}

/**
 * Generates a density heatmap for departmental availability
 */
function generateDepartmentHeatmap($timetable) {
    $heatmap = [];
    $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
    $periods = [
        "09:30 - 10:20", "10:20 - 11:10", "11:10 - 12:00", "12:00 - 12:50",
        "01:30 - 02:15", "02:15 - 03:00", "03:00 - 03:45", "03:45 - 04:30"
    ];
    
    foreach ($days as $day) {
        $heatmap[$day] = [];
        foreach ($periods as $period) {
            $count = count($timetable[$day][$period] ?? []);
            $heatmap[$day][$period] = $count;
        }
    }
    
    return $heatmap;
}
?>
