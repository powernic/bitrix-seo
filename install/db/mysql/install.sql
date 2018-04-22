create table if not exists b_powernic_seo
(
	`KEY` varchar(255) NOT NULL,
	`VALUE` varchar(255) NOT NULL,
	primary key (`KEY`)
);

INSERT INTO b_powernic_seo (`KEY`, `VALUE`) VALUES
('og:locale', 'ru_RU'),
('og:type', 'article'),
('og:site_name', ''),
('twitter:card', 'summary_large_image')
;