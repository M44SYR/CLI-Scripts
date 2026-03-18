"""
CLI Task Tracking System

Features:
- add task
- remove task
- view task
- mark as complete

Built as a Python practice exercise focusing on dictionaries
and program control flow.
"""
#M44SYR
#----------
# Imports
#----------

import sys

#----------
# Dictionaries
#----------

tasks = {}

#----------
# Helpers
#----------

if not tasks:
    HEADERS = ["ID", "Name", "Status"]
else:
    HEADERS = ["ID"] + list(next(iter(tasks.values())).keys())  #Auto detect Headers from inner keys, breaks if dict empty made helper gaurd above

#----------
# Constants
#----------

COLUMNS = "{:<5} {:<38} {:<10}" #Column widths
#HEADERS = Declared in helper above 
NAME = "name" #Tasks dict
STATUS = "status" #Tasks dict
TICK = " ✅"
CAUTION = " ⚠️"

#----------
# Functions 
#----------

def view_tasks(tasks) -> None:
    print(f"All Tasks")
    print(COLUMNS.format(*HEADERS))
    for task, task_info in tasks.items():
        print(COLUMNS.format(task, task_info[NAME], task_info[STATUS]))
        
def view_completed_tasks(tasks) -> None:
    print(f"Completed Tasks")
    print(COLUMNS.format(*HEADERS))
    for task, task_info in tasks.items():
        if task_info[STATUS] == "complete":
            print(COLUMNS.format(task, task_info[NAME], task_info[STATUS]+TICK))
            
def view_todo(tasks) -> None:
    todo_count = 0
    print(f"Outstanding Tasks")
    print(COLUMNS.format(*HEADERS))
    for task, task_info in tasks.items():
        if task_info[STATUS] != "complete":
            todo_count +=1
            print(COLUMNS.format(task, task_info[NAME], task_info[STATUS]+CAUTION))
    print(f"You have {todo_count} outstanding task(s)")

def add_task(tasks) -> None:
    last_task = []
    for k in tasks.keys():
        last_task.append(int(k[1:])) #strip the T prefix from the Task ID so can convert to int without error, then logic can be applied to generate new key
    if not last_task:
        new_num = 1
    else:
        new_num = max(last_task)+1
    if new_num < 10:
        add_task_id = f"T00{new_num}"
    elif new_num < 100:
        add_task_id = f"T0{new_num}"
    elif new_num < 1000:
        add_task_id = f"T{new_num}"
    else:
        print("Error, task ID's exceeded 4 characters")
    new_task = input(f"Please enter a task")
    tasks[add_task_id] = {"name" : new_task , "status" : "pending"}

def remove_task(tasks):
    del_task = input(f"Please enter Task ID to delete.")
    if del_task in tasks.keys():
        tasks.pop(del_task)
        print(f"{del_task} has successfully been deleted")
    else:
        print(f"Task ID not found, please enter a valid TASK ID T---")

def update_task(tasks):
    task_selection = input(f"Please enter Task ID to update")
    if task_selection in tasks.keys():
        update_selection = input(f"What would you like to update?\n [1] Status\n [2] Task")
        if update_selection == "1":
            if tasks[task_selection][STATUS] == "pending":
                tasks[task_selection][STATUS] = "complete"
                print(f"Status updated successfully")
            else:
                tasks[task_selection][STATUS] = "pending"
                print(f"Status updated successfully")
        elif update_selection == "2":
            tasks[task_selection][NAME] = input(f"Enter replacement task")
            print(f"Task updated successfully")
        else:
            print(f"Please enter either 1 or 2")
        
def menu_loop(selection,tasks):
    if selection == "1":
        sub_select = input(f"\nPlease select an option:\n\n1.View all tasks\n2.View todo list\n3.View completed tasks\n\nEnter to submit..")
        if sub_select == "1":
            view_tasks(tasks)
        elif sub_select == "2":
            view_todo(tasks)
        elif sub_select == "3":
            view_completed_tasks(tasks)
        else:
            print(f"\nPlease enter a number between 1-3\n")
    elif selection == "2":
        add_task(tasks)
    elif selection == "3":
        update_task(tasks)
    elif selection == "4":
        remove_task(tasks)
    elif selection == "5":
        leave = input(f"\nEnter 5 again to exit or press enter to go back to menu\n")
        if leave == "5":
            sys.exit() #imported sys  to exceute an exit
        else:
            return 
#user input thats passed into menu_loop()            
def menu_selection() -> str:
    menu = ["1", "2", "3", "4", "5" ]
    while True:
        selection = input(f"\nPlease select and option:\n\n1.View Tasks \n2.Add Task\n3.Update Task\n4.Remove Task\n5.Exit\n\nEnter to submit..")            
        if selection in menu:                
            return selection
        else:
            print("\nPlease enter a number between 1-5\n")
                            
#==========
# Events
#==========                           
"""_summary_
This is the main event loop of the CLI Task Tracking System. 
It continuously prompts the user for a menu selection and executes the corresponding function based on the user's input. 
The loop will continue until the user chooses to exit by entering '5' in the menu.
"""
while True:
    selection = menu_selection()
    menu_loop(selection,tasks)

