import mysql.connector

#Connection to Mysql. Make sure port is correct. 
cnx = mysql.connector.connect(user='root', password='root',
                              host='localhost',
                              port=8889,
                              database='dbf_project')

cursor = cnx.cursor()                    

#Create users
file = open('users.csv', 'r')
for line in file:
    values = line.split(',')
    add_user = ("INSERT INTO Users "
               "(username, seller, pass, email) "
               "VALUES (%s, %s, %s, %s)")
    data_user = (values[0], True if values[1] == 'true' else False, values[2], values[3])
    cursor.execute(add_user, data_user)

#Create categories
file = open('categories.csv', 'r').read()
categories = file.split(',')
add_category = ("INSERT INTO Categories "
               "(category) "
               "VALUES (%s)")
for category in categories:
    cursor.execute(add_category, (category,))

cnx.commit()
cnx.close()
