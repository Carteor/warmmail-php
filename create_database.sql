CREATE DATABASE mail;

USE mail;

CREATE TABLE users
(
  username    CHAR(16)  NOT NULL PRIMARY KEY,
  password    CHAR(40)  NOT NULL,
  address     CHAR(100) NOT NULL,
  displayname CHAR(100) NOT NULL
);

CREATE TABLE accounts
(
  username       CHAR(16)     NOT NULL,
  server         CHAR(100)    NOT NULL,
  port           INT          NOT NULL,
  type           CHAR(4)      NOT NULL,
  remoteuser     CHAR(50)     NOT NULL,
  remotepassword CHAR(50)     NOT NULL,
  accountid      INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY
);

GRANT SELECT, INSERT, UPDATE, DELETE
ON mail.*
TO mail@localhost
IDENTIFIED BY 'password';