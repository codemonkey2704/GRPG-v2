SET AUTOCOMMIT = 0;
START TRANSACTION;

DROP TABLE IF EXISTS `5050game`;
CREATE TABLE IF NOT EXISTS `5050game`
(
    `id`      int(11) NOT NULL PRIMARY KEY AUTO_INCREMENT,
    `owner`   int(11) NOT NULL DEFAULT 0,
    `amount`  int(11) NOT NULL DEFAULT 0,
    `pamount` int(11) NOT NULL DEFAULT 0

) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4;

DROP TABLE IF EXISTS `addptmarketlog`;
CREATE TABLE IF NOT EXISTS `addptmarketlog`
(
    `id`         int(11)   NOT NULL PRIMARY KEY AUTO_INCREMENT,
    `owner`      int(11)   NOT NULL DEFAULT 0,
    `amount`     int(11)   NOT NULL DEFAULT 0,
    `price`      int(11)   NOT NULL DEFAULT 0,
    `time_added` timestamp NOT NULL DEFAULT current_timestamp(),
    KEY (`owner`),
    KEY (`time_added`)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4;

DROP TABLE IF EXISTS `ads`;
CREATE TABLE IF NOT EXISTS `ads`
(
    `time_added` timestamp    NOT NULL DEFAULT current_timestamp(),
    `poster`     int(10)      NOT NULL,
    `title`      varchar(100) NOT NULL,
    `message`    text         NOT NULL

) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4;

DROP TABLE IF EXISTS `attlog`;
CREATE TABLE IF NOT EXISTS `attlog`
(
    `id`         int(11)   NOT NULL PRIMARY KEY AUTO_INCREMENT,
    `gangid`     int(11)   NOT NULL DEFAULT 0,
    `attacker`   int(11)   NOT NULL DEFAULT 0,
    `defender`   int(11)   NOT NULL DEFAULT 0,
    `winner`     int(11)   NOT NULL DEFAULT 0,
    `gangexp`    int(11)   NOT NULL DEFAULT 0,
    `active`     int(11)   NOT NULL DEFAULT 0,
    `time_added` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4;

DROP TABLE IF EXISTS `bans`;
CREATE TABLE IF NOT EXISTS `bans`
(
    `uni_id`     int(11)                               NOT NULL PRIMARY KEY AUTO_INCREMENT,
    `id`         int(11)                               NOT NULL DEFAULT 0,
    `days`       int(11)                               NOT NULL DEFAULT 0,
    `type`       enum ('perm','freeze','mail','forum') NOT NULL,
    `reason`     varchar(191)                          NOT NULL DEFAULT '',
    `time_added` timestamp                             NOT NULL DEFAULT current_timestamp()
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4;

DROP TABLE IF EXISTS `buyptmarketlog`;
CREATE TABLE IF NOT EXISTS `buyptmarketlog`
(
    `id`         int(11)   NOT NULL PRIMARY KEY AUTO_INCREMENT,
    `owner`      int(11)   NOT NULL DEFAULT 0,
    `amount`     int(11)   NOT NULL DEFAULT 0,
    `price`      int(11)   NOT NULL DEFAULT 0,
    `buyer`      int(11)   NOT NULL DEFAULT 0,
    `time_added` timestamp NOT NULL DEFAULT current_timestamp(),
    KEY (`owner`),
    KEY (`buyer`)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4;

DROP TABLE IF EXISTS `carlot`;
CREATE TABLE IF NOT EXISTS `carlot`
(
    `id`          int(11)      NOT NULL PRIMARY KEY AUTO_INCREMENT,
    `name`        varchar(191) NOT NULL DEFAULT '',
    `cost`        int(11)      NOT NULL DEFAULT 0,
    `image`       varchar(191) NOT NULL DEFAULT 'images/noimage.png',
    `buyable`     tinyint(1)   NOT NULL DEFAULT 0,
    `description` text         NOT NULL,
    `basemod`     int(11)      NOT NULL DEFAULT 0,
    `level`       int(11)      NOT NULL DEFAULT 0

) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4;

DROP TABLE IF EXISTS `cars`;
CREATE TABLE IF NOT EXISTS `cars`
(
    `id`     int(11) NOT NULL PRIMARY KEY AUTO_INCREMENT,
    `userid` int(11) NOT NULL DEFAULT 0,
    `carid`  int(11) NOT NULL DEFAULT 0

) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4;

DROP TABLE IF EXISTS `cash5050game`;
CREATE TABLE IF NOT EXISTS `cash5050game`
(
    `id`      int(11) NOT NULL PRIMARY KEY AUTO_INCREMENT,
    `owner`   int(11) NOT NULL DEFAULT 0,
    `amount`  int(11) NOT NULL DEFAULT 0,
    `pamount` int(11) NOT NULL DEFAULT 0

) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4;

DROP TABLE IF EXISTS `cash5050log`;
CREATE TABLE IF NOT EXISTS `cash5050log`
(
    `id`         int(11)      NOT NULL PRIMARY KEY AUTO_INCREMENT,
    `betterip`   varchar(191) NOT NULL DEFAULT '0.0.0.0',
    `matcherip`  varchar(191) NOT NULL DEFAULT '0.0.0.0',
    `winner`     int(11)      NOT NULL DEFAULT 0,
    `better`     int(11)      NOT NULL DEFAULT 0,
    `matcher`    int(11)      NOT NULL DEFAULT 0,
    `amount`     int(11)      NOT NULL DEFAULT 0,
    `time_added` timestamp    NOT NULL DEFAULT current_timestamp()
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4;

DROP TABLE IF EXISTS `cashlottery`;
CREATE TABLE IF NOT EXISTS `cashlottery`
(
    `id`     int(11) NOT NULL PRIMARY KEY AUTO_INCREMENT,
    `userid` int(11) NOT NULL DEFAULT 0
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4;

DROP TABLE IF EXISTS `cities`;
CREATE TABLE IF NOT EXISTS `cities`
(
    `id`          int(11)      NOT NULL PRIMARY KEY AUTO_INCREMENT,
    `name`        varchar(191) NOT NULL DEFAULT '',
    `levelreq`    int(11)      NOT NULL DEFAULT 0,
    `landleft`    int(11)      NOT NULL DEFAULT 0,
    `landprice`   int(11)      NOT NULL DEFAULT 0,
    `description` text         NOT NULL,
    `price`       int(11)      NOT NULL DEFAULT 0
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4;

DROP TABLE IF EXISTS `contactlist`;
CREATE TABLE IF NOT EXISTS `contactlist`
(
    `id`        int(11) NOT NULL PRIMARY KEY AUTO_INCREMENT,
    `playerid`  int(11) NOT NULL DEFAULT 0,
    `contactid` int(11) NOT NULL DEFAULT 0,
    `type`      int(11) NOT NULL DEFAULT 0
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4;

DROP TABLE IF EXISTS `contactmessages`;
CREATE TABLE IF NOT EXISTS `contactmessages`
(
    `id`         int(11)      NOT NULL PRIMARY KEY AUTO_INCREMENT,
    `email`      varchar(191) NOT NULL,
    `subject`    varchar(75)  NOT NULL,
    `message`    text         NOT NULL,
    `timeposted` timestamp    NOT NULL DEFAULT current_timestamp()
) ENGINE = InnoDB
  DEFAULT CHARSET = latin1;

DROP TABLE IF EXISTS `countries`;
CREATE TABLE IF NOT EXISTS `countries`
(
    `id`          int(11)      NOT NULL PRIMARY KEY AUTO_INCREMENT,
    `name`        varchar(191) NOT NULL DEFAULT '',
    `levelreq`    int(11)      NOT NULL DEFAULT 0,
    `rmonly`      int(11)      NOT NULL DEFAULT 0,
    `description` text         NOT NULL,
    `show`        int(11)      NOT NULL DEFAULT 0
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4;

DROP TABLE IF EXISTS `courses`;
CREATE TABLE IF NOT EXISTS `courses`
(
    `id`     int(11) NOT NULL PRIMARY KEY AUTO_INCREMENT,
    `course` int(11) NOT NULL,
    `user`   int(11) NOT NULL,
    KEY (`course`),
    KEY (`user`)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4;

DROP TABLE IF EXISTS `crimes`;
CREATE TABLE IF NOT EXISTS `crimes`
(
    `id`    int(11)      NOT NULL PRIMARY KEY AUTO_INCREMENT,
    `name`  varchar(191) NOT NULL DEFAULT '',
    `nerve` int(11)      NOT NULL DEFAULT 0,
    `stext` text         NOT NULL,
    `ftext` text         NOT NULL,
    `ctext` text         NOT NULL

) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4;

DROP TABLE IF EXISTS `deflog`;
CREATE TABLE IF NOT EXISTS `deflog`
(
    `id`         int(11)   NOT NULL PRIMARY KEY AUTO_INCREMENT,
    `gangid`     int(11)   NOT NULL DEFAULT 0,
    `attacker`   int(11)   NOT NULL DEFAULT 0,
    `defender`   int(11)   NOT NULL DEFAULT 0,
    `winner`     int(11)   NOT NULL DEFAULT 0,
    `gangexp`    int(11)   NOT NULL DEFAULT 0,
    `active`     int(11)   NOT NULL DEFAULT 0,
    `time_added` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4;

DROP TABLE IF EXISTS `druglords`;
CREATE TABLE IF NOT EXISTS `druglords`
(
    `id`          int(11)      NOT NULL PRIMARY KEY AUTO_INCREMENT,
    `name`        varchar(191) NOT NULL DEFAULT '0',
    `description` text         NOT NULL,
    `image`       varchar(191) NOT NULL DEFAULT '',
    `cost`        int(11)      NOT NULL DEFAULT 0
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4;

DROP TABLE IF EXISTS `effects`;
CREATE TABLE IF NOT EXISTS `effects`
(
    `id`       int(11)      NOT NULL PRIMARY KEY AUTO_INCREMENT,
    `userid`   int(11)      NOT NULL DEFAULT 0,
    `effect`   varchar(191) NOT NULL DEFAULT '',
    `timeleft` int(11)      NOT NULL DEFAULT 0
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4;

DROP TABLE IF EXISTS `events`;
CREATE TABLE IF NOT EXISTS `events`
(
    `id`         int(11)   NOT NULL PRIMARY KEY AUTO_INCREMENT,
    `recipient`  int(11)   NOT NULL DEFAULT 0,
    `time_added` timestamp NOT NULL DEFAULT current_timestamp(),
    `content`    text      NOT NULL,
    `extra`      int(11)   NOT NULL DEFAULT 0,
    `viewed`     int(11)   NOT NULL DEFAULT 0
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4;

DROP TABLE IF EXISTS `forgot_password`;
CREATE TABLE IF NOT EXISTS `forgot_password`
(
    `id`         int(11)      NOT NULL PRIMARY KEY AUTO_INCREMENT,
    `userid`     int(11)      NOT NULL DEFAULT 0,
    `email`      varchar(191) NOT NULL DEFAULT '',
    `token`      varchar(191) NOT NULL DEFAULT '',
    `time_added` timestamp    NOT NULL DEFAULT current_timestamp()
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4;

DROP TABLE IF EXISTS `forum_boards`;
CREATE TABLE IF NOT EXISTS `forum_boards`
(
    `fb_id`            int(11)                          NOT NULL PRIMARY KEY AUTO_INCREMENT,
    `fb_name`          varchar(191)                     NOT NULL,
    `fb_desc`          varchar(191)                     NOT NULL,
    `fb_auth`          enum ('public','staff','family') NOT NULL DEFAULT 'public',
    `fb_bin`           tinyint(1)                       NOT NULL DEFAULT 0,
    `fb_topics`        int(11)                          NOT NULL DEFAULT 0,
    `fb_posts`         int(11)                          NOT NULL DEFAULT 0,
    `fb_latest_topic`  int(11)                          NOT NULL DEFAULT 0,
    `fb_latest_post`   int(11)                          NOT NULL DEFAULT 0,
    `fb_latest_poster` int(11)                          NOT NULL DEFAULT 0,
    `fb_latest_time`   timestamp                        NULL,
    `fb_owner`         int(11)                          NOT NULL DEFAULT 0,
    KEY (`fb_name`),
    KEY (`fb_bin`),
    KEY (`fb_auth`)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4;

DROP TABLE IF EXISTS `forum_browsers`;
CREATE TABLE IF NOT EXISTS `forum_browsers`
(
    `id`     int(11)      NOT NULL PRIMARY KEY AUTO_INCREMENT,
    `userid` int(11)      NOT NULL DEFAULT 0,
    `name`   varchar(191) NOT NULL DEFAULT ''
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4;

DROP TABLE IF EXISTS `forum_posts`;
CREATE TABLE IF NOT EXISTS `forum_posts`
(
    `fp_id`          int(11)      NOT NULL PRIMARY KEY AUTO_INCREMENT,
    `fp_board`       int(11)      NOT NULL DEFAULT 0,
    `fp_topic`       int(11)      NOT NULL DEFAULT 0,
    `fp_time`        timestamp    NOT NULL DEFAULT current_timestamp(),
    `fp_poster`      int(11)      NOT NULL DEFAULT 0,
    `fp_text`        text         NOT NULL,
    `fp_edit_times`  smallint(8)  NOT NULL DEFAULT 0,
    `fp_edit_reason` varchar(191) NOT NULL DEFAULT '',
    `fp_edit_time`   timestamp    NULL,
    KEY (`fp_board`),
    KEY (`fp_topic`)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4;

DROP TABLE IF EXISTS `forum_subscriptions`;
CREATE TABLE IF NOT EXISTS `forum_subscriptions`
(
    `id`          int(11)   NOT NULL PRIMARY KEY AUTO_INCREMENT,
    `userid`      int(11)   NOT NULL DEFAULT 0,
    `topic`       int(11)   NOT NULL DEFAULT 0,
    `date_subbed` timestamp NOT NULL DEFAULT current_timestamp(),
    KEY (`userid`)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4;

DROP TABLE IF EXISTS `forum_topics`;
CREATE TABLE IF NOT EXISTS `forum_topics`
(
    `ft_id`            int(11)      NOT NULL PRIMARY KEY AUTO_INCREMENT,
    `ft_board`         int(11)      NOT NULL DEFAULT 0,
    `ft_name`          varchar(191) NOT NULL,
    `ft_creation_time` timestamp    NOT NULL DEFAULT current_timestamp(),
    `ft_creation_user` int(11)      NOT NULL DEFAULT 0,
    `ft_latest_time`   timestamp    NULL,
    `ft_latest_user`   int(11)      NOT NULL DEFAULT 0,
    `ft_latest_post`   int(11)      NOT NULL DEFAULT 0,
    `ft_pinned`        tinyint(1)   NOT NULL DEFAULT 0,
    `ft_locked`        tinyint(1)   NOT NULL DEFAULT 0,
    KEY (`ft_board`),
    KEY (`ft_pinned`),
    KEY (`ft_locked`)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4;

DROP TABLE IF EXISTS `gangarmory`;
CREATE TABLE IF NOT EXISTS `gangarmory`
(
    `id`       int(11) NOT NULL PRIMARY KEY AUTO_INCREMENT,
    `gangid`   int(11) NOT NULL DEFAULT 0,
    `itemid`   int(11) NOT NULL DEFAULT 0,
    `quantity` int(11) NOT NULL DEFAULT 0
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4;

DROP TABLE IF EXISTS `gangcrime`;
CREATE TABLE IF NOT EXISTS `gangcrime`
(
    `id`        int(11)      NOT NULL PRIMARY KEY AUTO_INCREMENT,
    `name`      varchar(191) NOT NULL DEFAULT '',
    `duration`  int(11)      NOT NULL DEFAULT 0,
    `reward`    int(11)      NOT NULL DEFAULT 0,
    `members`   int(11)      NOT NULL DEFAULT 0,
    `expreward` int(11)      NOT NULL DEFAULT 0
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4;

DROP TABLE IF EXISTS `gangevents`;
CREATE TABLE IF NOT EXISTS `gangevents`
(
    `id`       int(11) NOT NULL PRIMARY KEY AUTO_INCREMENT,
    `gang`     int(11) NOT NULL DEFAULT 0,
    `timesent` int(11) NOT NULL DEFAULT 0,
    `text`     text    NOT NULL,
    `extra`    int(11) NOT NULL DEFAULT 0
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4;

DROP TABLE IF EXISTS `ganginvites`;
CREATE TABLE IF NOT EXISTS `ganginvites`
(
    `id`       int(11) NOT NULL PRIMARY KEY AUTO_INCREMENT,
    `playerid` int(11) NOT NULL DEFAULT 0,
    `gangid`   int(11) NOT NULL DEFAULT 0
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4;

DROP TABLE IF EXISTS ganglog;
CREATE TABLE ganglog
(
    id         INT       NOT NULL PRIMARY KEY AUTO_INCREMENT,
    gangid     INT       NOT NULL REFERENCES gangs (id),
    attacker   INT       NOT NULL REFERENCES users (id),
    defender   INT       NOT NULL REFERENCES users (id),
    winner     INT       NOT NULL REFERENCES users (id),
    time_added TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4;


DROP TABLE IF EXISTS `gangmail`;
CREATE TABLE IF NOT EXISTS `gangmail`
(
    `id`         int(100)  NOT NULL PRIMARY KEY AUTO_INCREMENT,
    `gangid`     int(11)   NOT NULL,
    `playerid`   int(11)   NOT NULL,
    `time_added` timestamp NOT NULL DEFAULT current_timestamp(),
    `subject`    text      NOT NULL,
    `body`       text      NOT NULL,
    KEY (`playerid`)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4;

DROP TABLE IF EXISTS `gangs`;
CREATE TABLE IF NOT EXISTS `gangs`
(
    `id`           int(11)      NOT NULL PRIMARY KEY AUTO_INCREMENT,
    `name`         varchar(191) NOT NULL DEFAULT '',
    `banner`       varchar(191) NOT NULL DEFAULT '',
    `description`  text         NOT NULL DEFAULT '',
    `publicpage`   text         NOT NULL DEFAULT '',
    `boughtbanner` int(11)      NOT NULL DEFAULT 0,
    `leader`       int(11)      NOT NULL DEFAULT 0,
    `capacity`     int(11)      NOT NULL DEFAULT 5,
    `tag`          varchar(3)   NOT NULL DEFAULT '',
    `level`        int(11)      NOT NULL DEFAULT 1,
    `experience`   bigint(25)   NOT NULL DEFAULT 0,
    `moneyvault`   bigint(25)   NOT NULL DEFAULT 0,
    `pointsvault`  bigint(25)   NOT NULL DEFAULT 0,
    `crime`        int(11)      NOT NULL DEFAULT 0,
    `ending`       int(11)      NOT NULL DEFAULT 0,
    `ghouse`       int(11)      NOT NULL DEFAULT 0,
    `tmstats`      int(11)      NOT NULL DEFAULT 0,
    `tax`          int(11)      NOT NULL DEFAULT 0,
    `crimestarter` int(11)      NOT NULL DEFAULT 0,
    `kills`        int(11)      NOT NULL DEFAULT 0
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4;

DROP TABLE IF EXISTS `gangwars`;
CREATE TABLE IF NOT EXISTS `gangwars`
(
    `id`         int(11)   NOT NULL PRIMARY KEY AUTO_INCREMENT,
    `gang1`      int(11)   NOT NULL DEFAULT 0,
    `gang2`      int(11)   NOT NULL DEFAULT 0,
    `accepted`   int(11)   NOT NULL DEFAULT 0,
    `time_added` timestamp NOT NULL DEFAULT current_timestamp(),
    `bet`        int(20)   NOT NULL DEFAULT 0,
    `time_ended` timestamp NULL,
    `warid`      int(11)   NOT NULL DEFAULT 0,
    `gang1score` int(100)  NOT NULL DEFAULT 0,
    `gang2score` int(100)  NOT NULL DEFAULT 0
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4;

DROP TABLE IF EXISTS `gang_loans`;
CREATE TABLE IF NOT EXISTS `gang_loans`
(
    `id`       int(11) NOT NULL PRIMARY KEY AUTO_INCREMENT,
    `to`       int(11) NOT NULL DEFAULT 0,
    `item`     int(11) NOT NULL DEFAULT 0,
    `quantity` int(11) NOT NULL DEFAULT 0
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4;

DROP TABLE IF EXISTS `gcrimelog`;
CREATE TABLE IF NOT EXISTS `gcrimelog`
(
    `id`        int(11)      NOT NULL PRIMARY KEY AUTO_INCREMENT,
    `gangid`    int(11)      NOT NULL DEFAULT 0,
    `timestamp` int(11)      NOT NULL DEFAULT 0,
    `text`      text         NOT NULL,
    `reward`    varchar(191) NOT NULL DEFAULT '',
    `userid`    int(11)      NOT NULL DEFAULT 0
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4;

DROP TABLE IF EXISTS `ghouses`;
CREATE TABLE IF NOT EXISTS `ghouses`
(
    `id`    int(11)      NOT NULL PRIMARY KEY AUTO_INCREMENT,
    `name`  varchar(191) NOT NULL DEFAULT '',
    `awake` int(11)      NOT NULL DEFAULT 0,
    `cost`  int(11)      NOT NULL DEFAULT 0,
    `tax`   int(11)      NOT NULL DEFAULT 0
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4;

DROP TABLE IF EXISTS `growing`;
CREATE TABLE IF NOT EXISTS `growing`
(
    `id`         int(11)      NOT NULL PRIMARY KEY AUTO_INCREMENT,
    `amount`     int(11)      NOT NULL DEFAULT 0,
    `cropamount` int(11)      NOT NULL DEFAULT 0,
    `userid`     int(11)      NOT NULL DEFAULT 0,
    `croptype`   varchar(191) NOT NULL DEFAULT '',
    `cityid`     int(11)      NOT NULL DEFAULT 0,
    `time_ended` TIMESTAMP    NULL
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4;

DROP TABLE IF EXISTS `houses`;
CREATE TABLE IF NOT EXISTS `houses`
(
    `id`      int(11)      NOT NULL PRIMARY KEY AUTO_INCREMENT,
    `name`    varchar(191) NOT NULL DEFAULT '',
    `awake`   int(11)      NOT NULL DEFAULT 100,
    `cost`    int(11)      NOT NULL DEFAULT 0,
    `buyable` int(11)      NOT NULL DEFAULT 0
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4;

DROP TABLE IF EXISTS `ignorelist`;
CREATE TABLE IF NOT EXISTS `ignorelist`
(
    `id`         int(11)   NOT NULL PRIMARY KEY AUTO_INCREMENT,
    `blocker`    int(11)   NOT NULL DEFAULT 0,
    `blocked`    int(11)   NOT NULL DEFAULT 0,
    `time_added` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4;

DROP TABLE IF EXISTS `inventory`;
CREATE TABLE IF NOT EXISTS `inventory`
(
    `id`       int(11) NOT NULL PRIMARY KEY AUTO_INCREMENT,
    `userid`   int(11) NOT NULL DEFAULT 0,
    `itemid`   int(11) NOT NULL DEFAULT 0,
    `quantity` int(11) NOT NULL DEFAULT 0
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4;

DROP TABLE IF EXISTS `ipn`;
CREATE TABLE IF NOT EXISTS `ipn`
(
    `id`            int(11)      NOT NULL PRIMARY KEY AUTO_INCREMENT,
    `user_id`       int(11)      NOT NULL DEFAULT 0,
    `creditsbought` int(11)      NOT NULL DEFAULT 0,
    `paymentamount` int(11)      NOT NULL DEFAULT 0,
    `payeremail`    varchar(191) NOT NULL DEFAULT '',
    `date`          int(11)      NOT NULL DEFAULT 0
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4;

DROP TABLE IF EXISTS `itemmarket`;
CREATE TABLE IF NOT EXISTS `itemmarket`
(
    `id`         int(11)   NOT NULL PRIMARY KEY AUTO_INCREMENT,
    `itemid`     int(11)   NOT NULL DEFAULT 0,
    `userid`     int(11)   NOT NULL DEFAULT 0,
    `cost`       int(11)   NOT NULL DEFAULT 0,
    `time_added` timestamp NOT NULL DEFAULT current_timestamp(),
    KEY (`itemid`),
    KEY (`userid`)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4;

DROP TABLE IF EXISTS `items`;
CREATE TABLE IF NOT EXISTS `items`
(
    `id`          int(11)      NOT NULL PRIMARY KEY AUTO_INCREMENT,
    `name`        varchar(191) NOT NULL DEFAULT '',
    `description` text         NOT NULL,
    `image`       varchar(191) NOT NULL DEFAULT '',
    `speed`       int(11)      NOT NULL DEFAULT 0,
    `defense`     int(11)      NOT NULL DEFAULT 0,
    `cost`        int(11)      NOT NULL DEFAULT 0,
    `offense`     int(11)      NOT NULL DEFAULT 0,
    `buyable`     int(11)      NOT NULL DEFAULT 0,
    `heal`        int(11)      NOT NULL DEFAULT 0,
    `level`       int(11)      NOT NULL DEFAULT 0,
    `drugstr`     int(11)      NOT NULL DEFAULT 0,
    `drugspe`     int(11)      NOT NULL DEFAULT 0,
    `drugdef`     int(11)      NOT NULL DEFAULT 0,
    `drugstime`   int(11)      NOT NULL DEFAULT 0,
    `reduce`      int(11)      NOT NULL DEFAULT 0,
    `petupgrades` int(11)      NOT NULL DEFAULT 0,
    `rare`        int(11)      NOT NULL DEFAULT 0,
    `rmdays`      int(11)      NOT NULL DEFAULT 0,
    `money`       int(11)      NOT NULL DEFAULT 0,
    `points`      int(11)      NOT NULL DEFAULT 0,
    `cid`         int(11)      NOT NULL DEFAULT 0
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4;

DROP TABLE IF EXISTS `jobs`;
CREATE TABLE IF NOT EXISTS `jobs`
(
    `id`       int(11)      NOT NULL PRIMARY KEY AUTO_INCREMENT,
    `name`     varchar(191) NOT NULL DEFAULT '',
    `money`    int(11)      NOT NULL DEFAULT 0,
    `strength` int(11)      NOT NULL DEFAULT 0,
    `defense`  int(11)      NOT NULL DEFAULT 0,
    `speed`    int(11)      NOT NULL DEFAULT 0,
    `level`    int(11)      NOT NULL DEFAULT 0
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4;

DROP TABLE IF EXISTS `land`;
CREATE TABLE IF NOT EXISTS `land`
(
    `id`     int(11) NOT NULL PRIMARY KEY AUTO_INCREMENT,
    `userid` int(11) NOT NULL DEFAULT 0,
    `city`   int(11) NOT NULL DEFAULT 0,
    `amount` int(11) NOT NULL DEFAULT 0
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4;

DROP TABLE IF EXISTS `lottery`;
CREATE TABLE IF NOT EXISTS `lottery`
(
    `id`     int(11) NOT NULL PRIMARY KEY AUTO_INCREMENT,
    `userid` int(20) NOT NULL

) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4;

DROP TABLE IF EXISTS `monthly_referrals`;
CREATE TABLE IF NOT EXISTS `monthly_referrals`
(
    `id`       int(11) NOT NULL PRIMARY KEY AUTO_INCREMENT,
    `MONTH_1`  int(11) NOT NULL DEFAULT 0,
    `MONTH_2`  int(11) NOT NULL DEFAULT 0,
    `MONTH_3`  int(11) NOT NULL DEFAULT 0,
    `MONTH_4`  int(11) NOT NULL DEFAULT 0,
    `MONTH_5`  int(11) NOT NULL DEFAULT 0,
    `MONTH_6`  int(11) NOT NULL DEFAULT 0,
    `MONTH_7`  int(11) NOT NULL DEFAULT 0,
    `MONTH_8`  int(11) NOT NULL DEFAULT 0,
    `MONTH_9`  int(11) NOT NULL DEFAULT 0,
    `MONTH_10` int(11) NOT NULL DEFAULT 0,
    `MONTH_11` int(11) NOT NULL DEFAULT 0,
    `MONTH_12` int(11) NOT NULL DEFAULT 0
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4;

DROP TABLE IF EXISTS `pending_validations`;
CREATE TABLE IF NOT EXISTS `pending_validations`
(
    `id`              int(11)      NOT NULL PRIMARY KEY AUTO_INCREMENT,
    `username`        varchar(191) NOT NULL DEFAULT '',
    `ip`              varchar(191) NOT NULL DEFAULT '',
    `password`        text         NOT NULL,
    `email`           varchar(191) NOT NULL DEFAULT '',
    `class`           varchar(191) NOT NULL DEFAULT '',
    `validation_code` varchar(191) NOT NULL DEFAULT '',
    `time_added`      timestamp    NOT NULL DEFAULT current_timestamp()
) ENGINE = InnoDB
  DEFAULT CHARSET = latin1;

DROP TABLE IF EXISTS `pms`;
CREATE TABLE IF NOT EXISTS `pms`
(
    `id`        int(11)      NOT NULL PRIMARY KEY AUTO_INCREMENT,
    `parent`    int(11)      NOT NULL DEFAULT 0,
    `sender`    int(11)      NOT NULL DEFAULT 0,
    `recipient` int(11)      NOT NULL DEFAULT 0,
    `timesent`  int(11)      NOT NULL DEFAULT 0,
    `subject`   varchar(191) NOT NULL DEFAULT '',
    `msgtext`   text         NOT NULL,
    `viewed`    int(11)      NOT NULL DEFAULT 0,
    `bomb`      int(11)      NOT NULL DEFAULT 0,
    `bombed`    int(11)      NOT NULL DEFAULT 0
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4;

DROP TABLE IF EXISTS `pointsmarket`;
CREATE TABLE IF NOT EXISTS `pointsmarket`
(
    `id`     int(11) NOT NULL PRIMARY KEY AUTO_INCREMENT,
    `owner`  int(11) NOT NULL DEFAULT 0,
    `amount` int(11) NOT NULL DEFAULT 0,
    `price`  int(11) NOT NULL DEFAULT 0,
    KEY (`owner`)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4;

DROP TABLE IF EXISTS `poll1`;
CREATE TABLE IF NOT EXISTS `poll1`
(
    `optionid` int(11) NOT NULL PRIMARY KEY AUTO_INCREMENT,
    `votes`    int(11) NOT NULL DEFAULT 0
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4;

DROP TABLE IF EXISTS `pts5050game`;
CREATE TABLE IF NOT EXISTS `pts5050game`
(
    `id`      int(11) NOT NULL PRIMARY KEY AUTO_INCREMENT,
    `owner`   int(11) NOT NULL DEFAULT 0,
    `amount`  int(11) NOT NULL DEFAULT 0,
    `pamount` int(11) NOT NULL DEFAULT 0,
    `live`    int(11) NOT NULL DEFAULT 0
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4;

DROP TABLE IF EXISTS `pts5050log`;
CREATE TABLE IF NOT EXISTS `pts5050log`
(
    `id`         int(11)      NOT NULL PRIMARY KEY AUTO_INCREMENT,
    `betterip`   varchar(191) NOT NULL DEFAULT '0.0.0.0',
    `matcherip`  varchar(191) NOT NULL DEFAULT '0.0.0.0',
    `winner`     int(11)      NOT NULL DEFAULT 0,
    `better`     int(11)      NOT NULL DEFAULT 0,
    `matcher`    int(11)      NOT NULL DEFAULT 0,
    `amount`     int(11)      NOT NULL DEFAULT 0,
    `time_added` timestamp    NOT NULL DEFAULT current_timestamp()
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4;

DROP TABLE IF EXISTS `ptslottery`;
CREATE TABLE IF NOT EXISTS `ptslottery`
(
    `id`     int(11) NOT NULL PRIMARY KEY AUTO_INCREMENT,
    `userid` int(11) NOT NULL DEFAULT 0
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4;

DROP TABLE IF EXISTS `ranks`;
CREATE TABLE IF NOT EXISTS `ranks`
(
    `id`           int(11)      NOT NULL PRIMARY KEY AUTO_INCREMENT,
    `gang`         int(11)      NOT NULL DEFAULT 0,
    `title`        varchar(191) NOT NULL DEFAULT '',
    `members`      int(11)      NOT NULL DEFAULT 0,
    `crime`        int(11)      NOT NULL DEFAULT 0,
    `vault`        int(11)      NOT NULL DEFAULT 0,
    `ranks`        int(11)      NOT NULL DEFAULT 0,
    `massmail`     int(11)      NOT NULL DEFAULT 0,
    `applications` int(11)      NOT NULL DEFAULT 0,
    `appearance`   int(11)      NOT NULL DEFAULT 0,
    `invite`       int(11)      NOT NULL DEFAULT 0,
    `houses`       int(11)      NOT NULL DEFAULT 0,
    `upgrade`      int(11)      NOT NULL DEFAULT 0,
    `gforum`       int(11)      NOT NULL DEFAULT 0,
    `polls`        int(11)      NOT NULL DEFAULT 0
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4;

DROP TABLE IF EXISTS `rating`;
CREATE TABLE IF NOT EXISTS `rating`
(
    `id`    int(11) NOT NULL PRIMARY KEY AUTO_INCREMENT,
    `user`  int(11) NOT NULL DEFAULT 0,
    `rater` int(11) NOT NULL DEFAULT 0
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4;

DROP TABLE IF EXISTS `referrals`;
CREATE TABLE IF NOT EXISTS `referrals`
(
    `id`         int(11)   NOT NULL PRIMARY KEY AUTO_INCREMENT,
    `referrer`   int(11)   NOT NULL DEFAULT 0,
    `referred`   int(11)   NOT NULL DEFAULT 0,
    `credited`   int(11)   NOT NULL DEFAULT 0,
    `viewed`     int(11)   NOT NULL DEFAULT 0,
    `time_added` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4;

DROP TABLE IF EXISTS `removeptmarketlog`;
CREATE TABLE IF NOT EXISTS `removeptmarketlog`
(
    `id`         int(11)   NOT NULL PRIMARY KEY AUTO_INCREMENT,
    `owner`      int(11)   NOT NULL DEFAULT 0,
    `amount`     int(11)   NOT NULL DEFAULT 0,
    `price`      int(11)   NOT NULL DEFAULT 0,
    `time_added` timestamp NOT NULL DEFAULT current_timestamp(),
    KEY (`owner`)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4;

DROP TABLE IF EXISTS `rmstore_ipn`;
CREATE TABLE IF NOT EXISTS `rmstore_ipn`
(
    `id`             int(11)                                 NOT NULL PRIMARY KEY AUTO_INCREMENT,
    `userid`         int(11)                                 NOT NULL DEFAULT 0,
    `recipient`      int(11)                                 NOT NULL DEFAULT 0,
    `transaction_id` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
    `payer_email`    varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
    `pack_id`        int(11)                                 NOT NULL DEFAULT 0,
    `pack_cost`      decimal(4, 2)                           NOT NULL DEFAULT 0.00,
    `time_purchased` timestamp                               NOT NULL DEFAULT current_timestamp(),
    `paid_amount`    decimal(11, 2)                          NOT NULL DEFAULT 0.00,
    `discount`       int(5)                                  NOT NULL DEFAULT 0
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `rmstore_packs`;
CREATE TABLE IF NOT EXISTS `rmstore_packs`
(
    `id`          int(11)                                 NOT NULL PRIMARY KEY AUTO_INCREMENT,
    `name`        varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
    `cost`        decimal(4, 2)                           NOT NULL DEFAULT 0.00,
    `days`        int(11)                                 NOT NULL DEFAULT 0,
    `money`       bigint(25)                              NOT NULL DEFAULT 0,
    `points`      bigint(25)                              NOT NULL DEFAULT 0,
    `prostitutes` int(11)                                 NOT NULL DEFAULT 0,
    `items`       text COLLATE utf8mb4_unicode_ci         NOT NULL,
    `enabled`     tinyint(4)                              NOT NULL DEFAULT 1
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `rmstore_packs_errors`;
CREATE TABLE IF NOT EXISTS `rmstore_packs_errors`
(
    `id`        int(11)                                               NOT NULL PRIMARY KEY AUTO_INCREMENT,
    `subject`   varchar(191) COLLATE utf8mb4_unicode_ci               NOT NULL DEFAULT '',
    `message`   text COLLATE utf8mb4_unicode_ci                       NOT NULL,
    `status`    enum ('pending','handled') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
    `time_sent` timestamp                                             NOT NULL DEFAULT current_timestamp()
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `serverconfig`;
CREATE TABLE IF NOT EXISTS `serverconfig`
(
    `ID`               int(11)     NOT NULL PRIMARY KEY AUTO_INCREMENT,
    `radio`            varchar(5)  NOT NULL DEFAULT '',
    `serverdown`       text        NULL,
    `messagefromadmin` text        NULL,
    `register_lock`    int(1)      NOT NULL DEFAULT 0,
    `gamename`         varchar(25) NOT NULL DEFAULT '',
    `link`             varchar(75) NOT NULL DEFAULT ''
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4;
INSERT INTO `serverconfig` (`ID`)
VALUES (1);

DROP TABLE IF EXISTS `sessions`;
CREATE TABLE IF NOT EXISTS `sessions`
(
    `session_id`     varchar(32) NOT NULL DEFAULT '',
    `hash`           varchar(32) NOT NULL DEFAULT '',
    `session_data`   blob        NOT NULL,
    `session_expire` int(11)     NOT NULL DEFAULT 0,
    `userid`         int(11)     NOT NULL DEFAULT 0
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4;

DROP TABLE IF EXISTS `settings`;
CREATE TABLE IF NOT EXISTS `settings`
(
    `settings`   int(11)      NOT NULL PRIMARY KEY AUTO_INCREMENT,
    `conf_name`  varchar(191) NOT NULL,
    `conf_value` varchar(191) NOT NULL

) ENGINE = InnoDB
  DEFAULT CHARSET = latin1;
INSERT INTO `settings` (`settings`, `conf_name`, `conf_value`)
VALUES (1, 'registration', 'open'),
       (2, 'bus_travel_cost', '5000');

DROP TABLE IF EXISTS `shares`;
CREATE TABLE IF NOT EXISTS `shares`
(
    `id`        int(11) NOT NULL PRIMARY KEY AUTO_INCREMENT,
    `companyid` int(11) NOT NULL DEFAULT 0,
    `userid`    int(11) NOT NULL DEFAULT 0,
    `amount`    int(11) NOT NULL DEFAULT 0
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4;

DROP TABLE IF EXISTS `shout_box`;
CREATE TABLE IF NOT EXISTS `shout_box`
(
    `id`         int(11)      NOT NULL PRIMARY KEY AUTO_INCREMENT,
    `user`       varchar(60)  NOT NULL,
    `message`    varchar(100) NOT NULL,
    `date_time`  timestamp    NOT NULL DEFAULT current_timestamp(),
    `ip_address` varchar(40)  NOT NULL

) ENGINE = InnoDB
  DEFAULT CHARSET = latin1;

DROP TABLE IF EXISTS `site_bans`;
CREATE TABLE IF NOT EXISTS `site_bans`
(
    `id`       int(11)                                 NOT NULL PRIMARY KEY AUTO_INCREMENT,
    `userid`   int(11)                                 NOT NULL,
    `reason`   varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
    `banner`   int(11)                                 NOT NULL,
    `bannedon` timestamp                               NOT NULL DEFAULT current_timestamp()
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `spylog`;
CREATE TABLE IF NOT EXISTS `spylog`
(
    `id`       int(10) NOT NULL DEFAULT 0,
    `spyid`    int(10) NOT NULL DEFAULT 0,
    `strength` int(10) NOT NULL DEFAULT 0,
    `defense`  int(10) NOT NULL DEFAULT 0,
    `speed`    int(10) NOT NULL DEFAULT 0,
    `bank`     int(30) NOT NULL DEFAULT 0,
    `points`   int(20) NOT NULL DEFAULT 0,
    `age`      int(20) NOT NULL DEFAULT 0,
    KEY (`id`),
    KEY (`spyid`)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4;

DROP TABLE IF EXISTS `staffapps`;
CREATE TABLE IF NOT EXISTS `staffapps`
(
    `ID`        int(11)     NOT NULL PRIMARY KEY AUTO_INCREMENT,
    `userid`    int(11)     NOT NULL,
    `timeon`    int(20)     NOT NULL,
    `pastexp`   int(11)     NOT NULL,
    `better`    varchar(75) NOT NULL DEFAULT '',
    `staffrole` varchar(75) NOT NULL DEFAULT ''
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4;

DROP TABLE IF EXISTS `staff_logs`;
CREATE TABLE IF NOT EXISTS `staff_logs`
(
    `id`        int(11) NOT NULL PRIMARY KEY AUTO_INCREMENT,
    `player`    int(11) NOT NULL DEFAULT 0,
    `text`      text    NOT NULL,
    `timestamp` int(11) NOT NULL DEFAULT 0,
    `extra`     int(11) NOT NULL DEFAULT 0
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4;

DROP TABLE IF EXISTS `stocks`;
CREATE TABLE IF NOT EXISTS `stocks`
(
    `id`           int(10)     NOT NULL PRIMARY KEY AUTO_INCREMENT,
    `company_name` varchar(75) NOT NULL,
    `cost`         int(10)     NOT NULL

) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4;

DROP TABLE IF EXISTS `ticketreplies`;
CREATE TABLE IF NOT EXISTS `ticketreplies`
(
    `id`                 int(11)   NOT NULL PRIMARY KEY AUTO_INCREMENT,
    `userid`             int(11)   NOT NULL DEFAULT 0,
    `ticketid`           int(11)   NOT NULL DEFAULT 0,
    `body`               text      NOT NULL,
    `time_added`         timestamp NOT NULL DEFAULT current_timestamp(),
    `time_last_response` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4;

DROP TABLE IF EXISTS `tickets`;
CREATE TABLE IF NOT EXISTS `tickets`
(
    `id`                 int(11)                                   NOT NULL PRIMARY KEY AUTO_INCREMENT,
    `userid`             int(11)                                   NOT NULL DEFAULT 0,
    `subject`            varchar(191)                              NOT NULL DEFAULT '',
    `body`               text                                      NOT NULL,
    `status`             enum ('open','pending','closed','locked') NOT NULL DEFAULT 'open',
    `time_added`         timestamp                                 NOT NULL DEFAULT current_timestamp(),
    `time_last_response` timestamp                                 NULL,
    KEY (`userid`)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4;

DROP TABLE IF EXISTS `tickets_responses`;
CREATE TABLE `tickets_responses`
(
    id         INT(11)   NOT NULL PRIMARY KEY AUTO_INCREMENT,
    userid     INT(11)   NOT NULL REFERENCES users (id),
    body       TEXT      NULL,
    ticket_id  INT(11)   NOT NULL REFERENCES tickets (id),
    time_added TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX (ticket_id)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4;

DROP TABLE IF EXISTS `todo`;
CREATE TABLE IF NOT EXISTS `todo`
(
    `id`         int(11)     NOT NULL PRIMARY KEY AUTO_INCREMENT,
    `content`    text        NOT NULL,
    `status`     smallint(8) NOT NULL DEFAULT 0,
    `time_added` timestamp   NOT NULL DEFAULT current_timestamp()
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4;

DROP TABLE IF EXISTS `uni`;
CREATE TABLE IF NOT EXISTS `uni`
(
    `id`       int(11) NOT NULL PRIMARY KEY AUTO_INCREMENT,
    `playerid` int(11) NOT NULL DEFAULT 0,
    `courseid` int(11) NOT NULL DEFAULT 0
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4;

DROP TABLE IF EXISTS `updates`;
CREATE TABLE IF NOT EXISTS `updates`
(
    `id`       int(11)     NOT NULL PRIMARY KEY AUTO_INCREMENT,
    `name`     varchar(75) NOT NULL DEFAULT '',
    `lastdone` int(11)     NOT NULL DEFAULT 0
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4;
INSERT INTO `updates` (`name`, `lastdone`)
VALUES ('1min', UNIX_TIMESTAMP()),
       ('5min', UNIX_TIMESTAMP()),
       ('1hour', UNIX_TIMESTAMP()),
       ('1day', UNIX_TIMESTAMP());

DROP TABLE IF EXISTS `users`;
CREATE TABLE IF NOT EXISTS `users`
(
    `id`              int(11)                        NOT NULL PRIMARY KEY AUTO_INCREMENT,
    `username`        varchar(191)                   NOT NULL DEFAULT '',
    `loginame`        varchar(191)                   NOT NULL DEFAULT '',
    `password`        varchar(191)                   NOT NULL DEFAULT '',
    `admin`           int(11)                        NOT NULL DEFAULT 0,
    `ban`             tinyint(4)                     NOT NULL DEFAULT 0,
    `lastactive`      timestamp                      NOT NULL DEFAULT current_timestamp(),
    `gang`            int(11)                        NOT NULL DEFAULT 0,
    `grank`           int(11)                        NOT NULL DEFAULT 0,
    `eqweapon`        int(11)                        NOT NULL DEFAULT 0,
    `eqarmor`         int(11)                        NOT NULL DEFAULT 0,
    `eqshoes`         int(11)                        NOT NULL DEFAULT 0,
    `drugused`        int(11)                        NOT NULL DEFAULT 0,
    `drugtime`        int(11)                        NOT NULL DEFAULT 0,
    `strength`        bigint(25)                     NOT NULL DEFAULT 10,
    `speed`           bigint(25)                     NOT NULL DEFAULT 10,
    `defense`         bigint(25)                     NOT NULL DEFAULT 10,
    `ip`              varchar(191)                   NOT NULL DEFAULT '0.0.0.0',
    `marijuana`       int(11)                        NOT NULL DEFAULT 0,
    `potseeds`        int(11)                        NOT NULL DEFAULT 0,
    `experience`      bigint(25)                     NOT NULL DEFAULT 0,
    `level`           int(11)                        NOT NULL DEFAULT 1,
    `money`           bigint(25)                     NOT NULL DEFAULT 1000,
    `bank`            bigint(25)                     NOT NULL DEFAULT 0,
    `banklog`         bigint(25)                     NOT NULL DEFAULT 0,
    `bankupgrade`     bigint(25)                     NOT NULL DEFAULT 0,
    `upgradetimes`    int(11)                        NOT NULL DEFAULT 0,
    `whichbank`       int(11)                        NOT NULL DEFAULT 0,
    `workexp`         int(11)                        NOT NULL DEFAULT 0,
    `hp`              int(11)                        NOT NULL DEFAULT 50,
    `energy`          int(11)                        NOT NULL DEFAULT 10,
    `nerve`           int(11)                        NOT NULL DEFAULT 5,
    `battlewon`       int(11)                        NOT NULL DEFAULT 0,
    `battlelost`      int(11)                        NOT NULL DEFAULT 0,
    `battlemoney`     int(11)                        NOT NULL DEFAULT 0,
    `crimesucceeded`  int(11)                        NOT NULL DEFAULT 0,
    `crimefailed`     int(11)                        NOT NULL DEFAULT 0,
    `crimemoney`      int(11)                        NOT NULL DEFAULT 0,
    `busts`           int(11)                        NOT NULL DEFAULT 0,
    `caught`          int(11)                        NOT NULL DEFAULT 0,
    `signuptime`      timestamp                      NOT NULL DEFAULT current_timestamp(),
    `points`          int(11)                        NOT NULL DEFAULT 0,
    `rmdays`          int(11)                        NOT NULL DEFAULT 0,
    `house`           int(11)                        NOT NULL DEFAULT 0,
    `awake`           int(11)                        NOT NULL DEFAULT 100,
    `email`           varchar(191)                   NOT NULL DEFAULT '',
    `quote`           varchar(75)                    NOT NULL DEFAULT 'No-Quote',
    `avatar`          varchar(191)                   NOT NULL DEFAULT 'images/noimage.png',
    `city`            int(11)                        NOT NULL DEFAULT 1,
    `jail`            int(11)                        NOT NULL DEFAULT 0,
    `job`             int(11)                        NOT NULL DEFAULT 0,
    `hospital`        int(11)                        NOT NULL DEFAULT 0,
    `searchdowntown`  int(11)                        NOT NULL DEFAULT 100,
    `gender`          enum ('Male','Female','Other') NOT NULL DEFAULT 'Male',
    `posts`           int(11)                        NOT NULL DEFAULT 0,
    `signature`       text                           NOT NULL DEFAULT '',
    `notepad`         text                           NOT NULL DEFAULT '',
    `voted1`          int(11)                        NOT NULL DEFAULT 0,
    `voted2`          int(11)                        NOT NULL DEFAULT 0,
    `voted3`          int(11)                        NOT NULL DEFAULT 0,
    `voted4`          int(11)                        NOT NULL DEFAULT 0,
    `tag`             varchar(191)                   NOT NULL DEFAULT '',
    `polled1`         int(11)                        NOT NULL DEFAULT 0,
    `threadtime`      int(11)                        NOT NULL DEFAULT 0,
    `viewedupdate`    int(11)                        NOT NULL DEFAULT 0,
    `gangmail`        int(11)                        NOT NULL DEFAULT 0,
    `refcount`        int(11)                        NOT NULL DEFAULT 0,
    `boxes_opened`    int(11)                        NOT NULL DEFAULT 20,
    `lastchase`       int(11)                        NOT NULL DEFAULT 0,
    `userBANKDAYS`    int(11)                        NOT NULL DEFAULT 0,
    `mail_ban`        int(11)                        NOT NULL DEFAULT 0,
    `chat_ban`        int(11)                        NOT NULL DEFAULT 0,
    `banned`          int(11)                        NOT NULL DEFAULT 0,
    `hwho`            int(11)                        NOT NULL DEFAULT 0,
    `hwhen`           varchar(191)                   NOT NULL DEFAULT '',
    `hhow`            varchar(191)                   NOT NULL DEFAULT '',
    `gangleader`      int(11)                        NOT NULL DEFAULT 0,
    `activate`        int(11)                        NOT NULL DEFAULT 0,
    `news`            int(11)                        NOT NULL DEFAULT 0,
    `total`           int(11)                        NOT NULL DEFAULT 30,
    `posttime`        int(11)                        NOT NULL DEFAULT 0,
    `reported`        int(11)                        NOT NULL DEFAULT 0,
    `referrals`       int(11)                        NOT NULL DEFAULT 0,
    `signupip`        varchar(191)                   NOT NULL DEFAULT '0.0.0.0',
    `gangcrimes`      int(11)                        NOT NULL DEFAULT 0,
    `codescorrect`    int(11)                                 DEFAULT 0,
    `notes`           varchar(75)                    NOT NULL DEFAULT '',
    `class`           varchar(25)                    NOT NULL DEFAULT 'Mastermind',
    `nodoze`          int(11)                        NOT NULL DEFAULT 0,
    `genericsteroids` int(11)                        NOT NULL DEFAULT 0,
    `cocaine`         int(11)                        NOT NULL DEFAULT 0,
    `hookers`         int(11)                        NOT NULL DEFAULT 0
) ENGINE = InnoDB
  AUTO_INCREMENT = 2
  DEFAULT CHARSET = latin1;

DROP TABLE IF EXISTS `users_blocked`;
CREATE TABLE IF NOT EXISTS `users_blocked`
(
    `id`         int(11)   NOT NULL PRIMARY KEY AUTO_INCREMENT,
    `userid`     int(11)   NOT NULL DEFAULT 0,
    `blocked_id` int(11)   NOT NULL DEFAULT 0,
    `comment`    text      NOT NULL,
    `time_added` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE = InnoDB
  DEFAULT CHARSET = latin1;

DROP TABLE IF EXISTS `users_votes`;
CREATE TABLE IF NOT EXISTS `users_votes`
(
    `id`     int(11) NOT NULL PRIMARY KEY AUTO_INCREMENT,
    `userid` int(11) NOT NULL DEFAULT 0,
    `site`   int(11) NOT NULL DEFAULT 0,
    KEY (`userid`)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4;

DROP TABLE IF EXISTS `vlog`;
CREATE TABLE IF NOT EXISTS `vlog`
(
    `id`        int(11) NOT NULL PRIMARY KEY AUTO_INCREMENT,
    `gangid`    int(11) NOT NULL DEFAULT 0,
    `timestamp` int(11) NOT NULL DEFAULT 0,
    `text`      text    NOT NULL,
    `userid`    int(11) NOT NULL DEFAULT 0
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4;

DROP TABLE IF EXISTS `voting_sites`;
CREATE TABLE IF NOT EXISTS `voting_sites`
(
    `id`                   int(11)      NOT NULL PRIMARY KEY AUTO_INCREMENT,
    `title`                varchar(191) NOT NULL DEFAULT 'No title',
    `url`                  varchar(191) NOT NULL DEFAULT '',
    `reward_cash`          bigint(25)   NOT NULL DEFAULT 0,
    `reward_points`        bigint(25)   NOT NULL DEFAULT 0,
    `reward_items`         varchar(191) NOT NULL DEFAULT '0',
    `reward_rmdays`        int(11)      NOT NULL DEFAULT 0,
    `req_account_days_min` int(11)      NOT NULL DEFAULT 0,
    `req_account_days_max` int(11)      NOT NULL DEFAULT 0,
    `req_rmdays`           int(11)      NOT NULL DEFAULT 0,
    `days_between_vote`    int(11)      NOT NULL DEFAULT 1,
    `enabled`              tinyint(4)   NOT NULL DEFAULT 1,
    `date_added`           timestamp    NOT NULL DEFAULT current_timestamp(),
    KEY (`enabled`)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4;
COMMIT;
