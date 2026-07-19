export type BlockData = {
type: string,
data: Record<string, any>,
};
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
links: LinkData[],
features: FeatureData[],
ui: Record<string, string>,
};
export type PageStatus = 'draft' | 'published';
export type SeoData = {
title: string | null,
description: string | null,
ogImage: string | null,
};
