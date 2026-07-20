export type BlockData = {
type: string,
data: Record<string, any>,
};
export type BrandColorData = {
name: string,
hex: string,
role: ColorRole | null,
};
export type ColorRole = 'primary' | 'secondary' | 'accent' | 'neutral' | 'background' | 'text';
export type FeatureData = {
icon: string | null,
title: string | null,
description: string | null,
to: string | null,
};
export type LinkData = {
label: string | null,
to: string | null,
href: string | null,
icon: string | null,
trailingIcon: string | null,
color: string,
variant: string,
};
export type PageData = {
slug: string,
title: string,
seo: SeoData | null,
blocks: BlockData[],
updatedAt: string | null,
};
export type PageListItemData = {
id: number,
slug: string,
title: string,
updatedAt: string | null,
};
export type PageSectionData = {
title: string,
headline: string | null,
description: string | null,
icon: string | null,
orientation: string,
reverse: boolean,
backgroundColor: string | null,
backgroundImage: string | null,
backgroundPosition: string,
image: string | null,
links: LinkData[],
features: FeatureData[],
ui: Record<string, string>,
};
export type PageStatus = 'draft' | 'published';
export type SemanticColorsData = {
enabled: boolean,
success: string | null,
warning: string | null,
error: string | null,
info: string | null,
};
export type SeoData = {
title: string | null,
description: string | null,
ogImage: string | null,
};
export type SiteGlobalsData = {
siteName: string,
year: number,
colors: BrandColorData[],
semanticColors: SemanticColorsData,
};
