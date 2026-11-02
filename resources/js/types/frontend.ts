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
    images: { src: string; alt: string }[];
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
    image: string;
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
    image?: string | null;
};

export type PageHeroData = {
    eyebrow?: string;
    title: string;
    lead?: string;
    cta?: NavLink;
    image?: string;
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

export type MachineDetailSpecItem = {
    label: string;
    value: string;
};

export type MachineDetailRelatedData = {
    title: string;
    items: MachineItem[];
};

export type MachineDetailPageProps = {
    site: SiteData;
    machine: MachineItem;
    status: string;
    specs: MachineDetailSpecItem[];
    checks: string[];
    related: MachineDetailRelatedData;
    cta: NavLink;
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
    image?: string | null;
};

export type ProductsData = {
    eyebrow: string;
    title: string;
    link: NavLink;
    items: ProductItem[];
};

export type ProductCatalogItem = {
    kind: string;
    name: string;
    desc: string;
    tags: string[];
    image?: string | null;
};

export type ProductCatalogData = {
    filters: string[];
    items: ProductCatalogItem[];
};

export type ProductsPageProps = {
    site: SiteData;
    hero: PageHeroData;
    catalog: ProductCatalogData;
};

export type MicroMarketsHowItWorksData = {
    eyebrow: string;
    title: string;
    steps: { title: string; text: string }[];
};

export type MicroMarketsEquipmentData = {
    eyebrow: string;
    title: string;
    items: { title: string; text: string }[];
};

export type MicroMarketsOfferingItem = {
    kind: string;
    name: string;
    image?: string | null;
};

export type MicroMarketsOfferingsData = {
    eyebrow: string;
    title: string;
    items: MicroMarketsOfferingItem[];
};

export type MicroMarketsCompareItem = {
    title: string;
    text: string;
    image: string;
    href: string;
};

export type MicroMarketsCompareData = {
    title: string;
    items: MicroMarketsCompareItem[];
};

export type MicroMarketsPageProps = {
    site: SiteData;
    hero: PageHeroData;
    howItWorks: MicroMarketsHowItWorksData;
    equipment: MicroMarketsEquipmentData;
    offerings: MicroMarketsOfferingsData;
    compare: MicroMarketsCompareData;
    faqs: FaqsData;
};

export type OfficeCoffeeMachineItem = {
    title: string;
    text: string;
    tag: string;
    image: string;
    href: string;
};

export type OfficeCoffeeMachinesData = {
    eyebrow: string;
    title: string;
    items: OfficeCoffeeMachineItem[];
};

export type OfficeCoffeeTypeItem = {
    label: string;
    href: string;
};

export type OfficeCoffeeTypesData = {
    eyebrow: string;
    title: string;
    items: OfficeCoffeeTypeItem[];
};

export type OfficeCoffeeServiceData = {
    eyebrow: string;
    title: string;
    items: { title: string; text: string; href: string }[];
};

export type OfficeCoffeeRequestFormData = {
    title: string;
    text: string;
    successMessage: string;
    submitLabel: string;
    subjectLabel: string;
};

export type OfficeCoffeePageProps = {
    site: SiteData;
    hero: PageHeroData;
    machines: OfficeCoffeeMachinesData;
    types: OfficeCoffeeTypesData;
    service: OfficeCoffeeServiceData;
    requestForm: OfficeCoffeeRequestFormData;
};

export type ContactPanelData = {
    title: string;
    phone: string;
    phoneHref: string;
    email: string;
    emailHref: string;
    hours: string;
    operatorTitle: string;
    operatorLinks: NavLink[];
};

export type ContactFormData = {
    successMessage: string;
    submitLabel: string;
    topics: string[];
};

export type ContactPageProps = {
    site: SiteData;
    hero: PageHeroData;
    panel: ContactPanelData;
    form: ContactFormData;
};

export type AboutStoryData = {
    title: string;
    paragraphs: string[];
    valuesTitle: string;
    values: string[];
    kpis: { label: string; value: string }[];
};

export type AboutCtaData = {
    title: string;
    primary: NavLink;
    secondary: NavLink;
};

export type AboutPageProps = {
    site: SiteData;
    hero: PageHeroData;
    story: AboutStoryData;
    cta: AboutCtaData;
};

export type IndustriesIndexItem = {
    title: string;
    text: string;
    image: string;
    href: string;
    slug: string;
};

export type IndustriesPageProps = {
    site: SiteData;
    hero: PageHeroData;
    items: IndustriesIndexItem[];
};

export type IndustryShowProps = {
    site: SiteData;
    hero: PageHeroData;
    crumb: NavLink[];
    industry: { slug: string; label: string };
    benefits: {
        title: string;
        items: { title: string; text: string }[];
    };
    relatedMachines: MachineDetailRelatedData;
    requestForm: {
        title: string;
        text: string;
        successMessage: string;
        submitLabel: string;
        subjectLabel: string;
    };
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
