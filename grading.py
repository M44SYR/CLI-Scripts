"""
CLI Student Grading System

Features:
- collect marks for a predefined list of students
- validate input (0–100 or skip)
- assign grades based on thresholds
- display student marks and grades
- calculate highest, lowest, and average marks

Built as a Python practice exercise focusing on:
- dictionaries
- lists
- input validation
- loops and control flow
- basic statistics
"""
#M44SYR

# ----------
# dicts
# ----------

studentIds = ["S001","S002","S003","S004","S005","S006","S007","S008","S009","S010"]


# ----------
# Constants
# ----------

A_GRADE = 70
B_GRADE = 60
C_GRADE = 50
D_GRADE = 40

# ----------
# functions
# ----------

# Marking Logic

def calculate_grade(marks) : 
    grades = {}
    for i, mark in marks.items() :
        if mark is None :
            grade = "-"
        elif mark >= A_GRADE :
            grade = "A"
        elif mark >= B_GRADE :
            grade = "B"
        elif mark >= C_GRADE :
            grade = "C" 
        elif mark >= D_GRADE :
            grade = "D"
        else :
            grade = "U"
        grades[i] = grade
    return grades

# Marking Function 

def get_valid_mark(studentIds):
    marks = {}
    for i in studentIds :
        while True :
            try :
                mark = input(f"Please enter score for {i}")
                if mark == "" :
                    marks[i] = None 
                    break
                else:
                    mark = int(mark)
                if mark >= 0 and mark <= 100 :
                    marks[i] = mark
                    break
                if mark > 100 or mark < 0 :
                    print("Please enter a number between 0-100")
            except ValueError : 
                print("Please enter a number between 0-100")
    return marks

#Print function 

def print_grades(studentIds, marks, grades):
    for i in studentIds :
        grade = grades[i]
        mark = marks[i]
        print(f"{i} - {mark} - {grade}")
        
def filter_out_none(marks):
    filteredMarks = []
    for mark in marks.values():
        if mark is not None :
            filteredMarks.append(mark)
    return filteredMarks
    
#events 

def main():
    marks = get_valid_mark(studentIds)
    grades = calculate_grade(marks)
    print_grades(studentIds, marks, grades)
    filteredMarks = filter_out_none(marks)
    if len(filteredMarks) == 0 :
        print(f"No valid marks were entered.")
    else:
        print(f"The lowest mark was:- {min(filteredMarks)}")
        print(f"The highest mark was:- {max(filteredMarks)}")
        average = round(sum(filteredMarks)/len(filteredMarks), 1)
        print(f"the average mark was:- {average}")

if __name__ == "__main__":
    main()