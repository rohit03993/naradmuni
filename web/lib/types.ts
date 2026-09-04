export type Category = {
  id: number;
  hindi_name: string;
  cat_url: string;
  metad: string | null;
  metat: string | null;
  parent: string | null;
  menu: string | null;
  short: number | null;
  latter: string | null;
  main_heading?: string | null;
};

export type Team = {
  t_id: number;
  name: string;
  email: string | null;
  designation: string | null;
  image: string | null;
  fb_link: string | null;
  tw_link: string | null;
};

export type NewsCard = {
  newsid: number;
  title: string;
  newsurl: string;
  image: string | null;
  short_description: string | null;
  date: string | null;
  time: string | null;
  hindi_name?: string | null;
  cat_url?: string | null;
  category?: string | number | null;
};

export type NewsArticle = NewsCard & {
  description: string | null;
  img_abt: string | null;
  status: string | null;
  team_id: number;
  metat: string | null;
  metad: string | null;
  newstype: string | null;
};

export type Ad = {
  link: string | null;
  image: string | null;
  title: string | null;
};

export type SitePage = {
  page: string;
  page_url: string;
};
