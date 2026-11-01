export type NavLink = {
    label: string;
    href: string;
    current?: boolean;
};

export type FooterColumn = {
    title: string;
    links: NavLink[];
};

export type SiteData = {
    name: string;
    tagline: string;
    phone: string;
    footerBlurb: string;
    copyright: string;
    topNav: NavLink[];
    operatorCta: NavLink;
    mainNav: NavLink[];
    headerCta: NavLink;
    footerColumns: FooterColumn[];
    legalLinks: NavLink[];
};

export type HeroData = {
    eyebrow: string;
    titleBefore: string;
    titleAccent: string;
    lede: string;
    bullets: string[];
    cta: NavLink;
    phoneLabel: string;
    trust: {
        stars: string;
        text: string;
        reviewsLabel: string;
        reviewsHref: string;
    };
    artKinds: string[];
};

export type HowItWorksData = {
    eyebrow: string;
    title: string;
    text: string;
    steps: { title: string; text: string }[];
};

export type ServiceItem = {
    title: string;
    text: string;
    href: string;
    art: string;
};

export type ServicesData = {
    eyebrow: string;
    title: string;
    items: ServiceItem[];
};

export type MachineItem = {
    id: string;
    name: string;
    kind: string;
    type: string;
    tags: string[];
    desc: string;
    href: string;
};

export type PageHeroData = {
    eyebrow?: string;
    title: string;
    lead?: string;
    cta?: NavLink;
    art?: string;
};

export type MachineLineupData = {
    title: string;
    text: string;
    filters: string[];
    items: MachineItem[];
};

export type MachineTechData = {
    title: string;
    items: { title: string; text: string }[];
};

export type MachineIndustryItem = {
    slug: string;
    label: string;
    text: string;
    href: string;
};

export type MachineIndustriesData = {
    eyebrow: string;
    title: string;
    items: MachineIndustryItem[];
};

export type MachinesPageProps = {
    site: SiteData;
    hero: PageHeroData;
    lineup: MachineLineupData;
    tech: MachineTechData;
    industries: MachineIndustriesData;
    faqs: FaqsData;
};

export type FeaturedMachinesData = {
    eyebrow: string;
    title: string;
    link: NavLink;
    items: MachineItem[];
};

export type ProductItem = {
    kind: string;
    name: string;
    href: string;
};

export type ProductsData = {
    eyebrow: string;
    title: string;
    link: NavLink;
    items: ProductItem[];
};

export type RequestFormData = {
    eyebrow: string;
    title: string;
    text: string;
    successMessage: string;
    submitLabel: string;
    locationTypes: string[];
    machineInterests: string[];
    machineCounts: string[];
    existingVending: string[];
};

export type ReviewItem = {
    stars: string;
    quote: string;
    role: string;
    org: string;
};

export type ReviewsData = {
    eyebrow: string;
    title: string;
    items: ReviewItem[];
};

export type IndustryItem = {
    slug: string;
    label: string;
    href: string;
};

export type IndustriesData = {
    eyebrow: string;
    title: string;
    text: string;
    items: IndustryItem[];
};

export type FaqItem = {
    q: string;
    a: string;
};

export type FaqsData = {
    eyebrow: string;
    title: string;
    items: FaqItem[];
};

export type ArticleItem = {
    id: string;
    cat: string;
    title: string;
    excerpt: string;
    href: string;
};

export type ArticlesData = {
    eyebrow: string;
    title: string;
    link: NavLink;
    items: ArticleItem[];
};

export type CityItem = {
    city: string;
    state: string;
    label: string;
    href: string;
};

export type CitiesData = {
    eyebrow: string;
    title: string;
    items: CityItem[];
};

export type HomePageProps = {
    site: SiteData;
    hero: HeroData;
    howItWorks: HowItWorksData;
    services: ServicesData;
    featuredMachines: FeaturedMachinesData;
    products: ProductsData;
    requestForm: RequestFormData;
    reviews: ReviewsData;
    industries: IndustriesData;
    faqs: FaqsData;
    articles: ArticlesData;
    cities: CitiesData;
};
