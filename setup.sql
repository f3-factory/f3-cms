CREATE TABLE IF NOT EXISTS pages (
        slug VARCHAR(255) NOT NULL,
        position INTEGER,
        title VARCHAR(255),
        contents TEXT,
        updated TIMESTAMP
			DEFAULT (strftime('%s',CURRENT_TIMESTAMP,'localtime')),
        commentable INTEGER,
        PRIMARY KEY(slug)
);
INSERT INTO pages (
        slug,position,title,contents
) VALUES (
        "",
        1,
        "Home",
        "This content management system is designed for instructional purposes and help you get started on the use of the Fat-Free Framework. It is not intended for deployment on production servers."
);
INSERT INTO pages (
        slug,position,title,contents
) VALUES (
        "about",
        2,
        "About",
        "Fat-Free Framework is a powerful yet easy-to-use PHP micro-framework designed to help you build dynamic and robust Web applications - fast!"
);
CREATE TABLE IF NOT EXISTS comments (
        slug VARCHAR(255) NOT NULL,
        posted TIMESTAMP
			DEFAULT (strftime('%s',CURRENT_TIMESTAMP,'localtime')),
        name VARCHAR(255),
        email VARCHAR(255),
        identicon TEXT,
        contents TEXT,
        PRIMARY KEY(slug,posted)
);