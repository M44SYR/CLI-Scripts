"""
CLI Library Lending System

Features:
- borrow books
- return books
- view inventory
- menu driven interface

Built as a Python practice exercise focusing on dictionaries
and program control flow.
"""
import sys
#---------
# Dictionaries 
#---------

#library(book_id, status)
library = {
"B101" : "available",
"B102" : "available",
"B103" : "available",
"B104" : "available",
"B105" : "available"
}

#----------
# Functions
#----------
#Pass in book_id from selction
def borrow_book(library, book_id):
    if book_id not in library:
        print(f"\nBook not found\n")
        return
    status = library[book_id] 
    if status == "borrowed":
        print(f"\nBook is not available\n")
    else:
        status = "borrowed"
        print(f"\nBook successfully loaned\n")
    library[book_id] = status

def return_book(library, book_id):
    if book_id not in library:
        print("\nBook not found\n")
        return
    status = library[book_id]
    if status == "available":
        print(f"\nBook is not on loan\n")
    else:
        status = "available"
        print("\nBook successfully returned\n")
    library[book_id] = status #directly update dict can then run option 3 to show the change

def display_books(library):
    print(f"\n-------- Books -------\n")
    for book_id, status in library.items(): 
        print(f"Book: {book_id} - {status}")
    print(f"\n---------------------\n")
#Menu loop tha directs which function to call        
def menu_loop(selection,library):
    if selection == "1":
        book_id = input(f"\nPlease enter Book ID\n")
        borrow_book(library, book_id)
    elif selection == "2":
        book_id = input(f"\nPlease enter Book ID\n")
        return_book(library,book_id)
    elif selection == "3":
        display_books(library)
    elif selection == "4":
        leave = input(f"\nEnter 4 again to exit or press enter to go back to menu\n")
        if leave == "4":
            sys.exit() #imported sys  to exceute an exit
        else:
            return 
#user input thats passed into menu_loop()            
def menu_selection() -> str:
    menu = ["1","2","3","4"]
    while True:
        selection = input(f"\nPlease select and option:\n\n1.Borrow Book \n2.Return Book\n3.View Books\n4.Exit\n\nEnter to submit..")            
        if selection in menu:                
            return selection
        else:
            print("\nPlease enter a number between 1-4\n")                
#==========
# Events
#==========                           
#menu loop 
while True:
    selection = menu_selection()
    menu_loop(selection,library)