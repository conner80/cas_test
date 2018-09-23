USE cas;

DELETE FROM `USERS`;

DELETE FROM `PRIZES`;

INSERT INTO `USERS`
(`NAME`, `PASS`)
VALUES('doe', MD5('doe1'));

INSERT INTO `PRIZES`            
(`TYPE`, `NAME`, `COUNT`, `POINTS`)
VALUES('1', 'Point(s)', 10000, 0);

INSERT INTO `PRIZES`            
(`TYPE`, `NAME`, `COUNT`, `POINTS`)
VALUES('2', 'Jaket', 100, 0);

INSERT INTO `PRIZES`            
(`TYPE`, `NAME`, `COUNT`, `POINTS`)
VALUES('2', 'Trousers', 500, 0);

INSERT INTO `PRIZES`            
(`TYPE`, `NAME`, `COUNT`, `POINTS`)
VALUES('2', 'Shoes', 100, 0);

INSERT INTO `PRIZES`            
(`TYPE`, `NAME`, `COUNT`, `POINTS`)
VALUES('2', 'Car', 50, 0);

INSERT INTO `PRIZES`            
(`TYPE`, `NAME`, `COUNT`, `POINTS`)
VALUES('2', 'House', 20, 0);

INSERT INTO `PRIZES`            
(`TYPE`, `NAME`, `COUNT`, `POINTS`)
VALUES('3', 'Money', 100000, 10);

