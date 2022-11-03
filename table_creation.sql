DROP DATABASE IF EXISTS dbf_project; #Drop database if already exists. 
CREATE DATABASE dbf_project;
USE dbf_project;

/*
-Users table in charge of storing the user information. 
-All users are buyers. If "seller" is true then the user is also a seller. 
*/
CREATE TABLE Users(
    username varchar(255) PRIMARY KEY,
    seller boolean,
    pass varchar(255),
    email varchar(255)
);

/*
-Names of all categories that an auction can have. 
*/
CREATE TABLE Categories(
	category varchar(255) PRIMARY KEY
);

/*
-This table stores all auctions. 
-An auction is owned by a seller. 
-An auction has a category defined in the "Categories" table. 
*/
CREATE TABLE Auctions(
	auctionId int unsigned PRIMARY KEY AUTO_INCREMENT, 
    username varchar(255),
    title varchar(255),
    details varchar(255),
    category varchar(255),
    startingPrice double, 
    reservePrice double,
    endDate timestamp,
    auctionImage varchar(100),
    FOREIGN KEY (username) REFERENCES Users(username),
    FOREIGN KEY (category) REFERENCES Categories(category)
);

/*
-This table holds the bids placed on an auction. 
-Each bid is placed by a user (doesn't matter if it's a seller) on a specific auction. 
*/
CREATE TABLE Bids(
	bidId int unsigned PRIMARY KEY AUTO_INCREMENT,
    username varchar(255),
    auctionId int unsigned, 
    bidPrice double,
    FOREIGN KEY (username) REFERENCES Users(username),
    FOREIGN KEY (auctionId) REFERENCES Auctions(auctionId)
);

/*
-Records of which users are watching which auctions. 
*/
CREATE TABLE Watchlist(
	auctionId int unsigned,
    username varchar(255), 
    PRIMARY KEY (auctionId, username),
    FOREIGN KEY (auctionId) REFERENCES Auctions(auctionId),
    FOREIGN KEY (username) REFERENCES Users(username)
);