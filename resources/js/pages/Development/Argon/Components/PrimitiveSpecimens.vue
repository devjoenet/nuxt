<script lang="ts" setup>
import { Alert } from "@/components/Ui/Alert";
import { Avatar } from "@/components/Ui/Avatar";
import { Badge } from "@/components/Ui/Badge";
import { Breadcrumbs, type BreadcrumbItem } from "@/components/Ui/Breadcrumbs";
import { Button, type ButtonSize, type ButtonVariant } from "@/components/Ui/Button";
import { ButtonGroup } from "@/components/Ui/ButtonGroup";
import { Card } from "@/components/Ui/Card";
import { Divider } from "@/components/Ui/Divider";
import { EmptyState } from "@/components/Ui/EmptyState";
import { Icon } from "@/components/Ui/Icon";
import { List } from "@/components/Ui/List";
import { Progress } from "@/components/Ui/Progress";
import { Skeleton } from "@/components/Ui/Skeleton";
import { Spinner } from "@/components/Ui/Spinner";
import { Stat } from "@/components/Ui/Stat";
import { Typography, type TypographyVariant } from "@/components/Ui/Typography";
import { uiTones } from "@/lib/uiTone";

const breadcrumbs: BreadcrumbItem[] = [
  { label: "Workspace", href: "#" },
  { label: "Components", href: "#" },
  { label: "Primitives", current: true },
];
const buttonVariants: ButtonVariant[] = ["solid", "soft", "outline", "ghost", "link"];
const buttonSizes: ButtonSize[] = ["xs", "sm", "md", "lg"];
const badgeVariants = ["solid", "soft", "outline"] as const;
const avatarSizes = ["xs", "sm", "md", "lg", "xl"] as const;
const alertVariants = ["soft", "outline", "solid"] as const;
const cardVariants = ["elevated", "outline", "soft", "glass", "naked"] as const;
const typographySamples: TypographyVariant[] = ["display", "h1", "h2", "h3", "h4", "body", "lead", "small", "caption", "eyebrow"];
const listVariants = ["plain", "bullet", "numbered", "divided"] as const;
const progressSizes = ["xs", "sm", "md", "lg"] as const;
const loadingSizes = ["xs", "sm", "md", "lg", "xl"] as const;
const skeletonShapes = ["line", "block", "circle"] as const;
</script>

<template>
  <section aria-labelledby="primitive-specimens" class="grid gap-6">
    <Typography id="primitive-specimens" as="h2" tone="heading" variant="h2">Core primitive specimens</Typography>
    <Typography as="h3" tone="heading" variant="h4">Button tones, variants, sizes, and states</Typography>
    <div class="flex flex-wrap gap-2">
      <Button v-for="tone in uiTones" :key="tone" :label="tone" :tone="tone" />
    </div>
    <div class="flex flex-wrap items-center gap-2"><Button v-for="variant in buttonVariants" :key="variant" :label="variant" :variant="variant" /></div>
    <div class="flex flex-wrap items-center gap-2">
      <Button v-for="size in buttonSizes" :key="size" :label="size" :size="size" /><Button label="Pill" shape="pill" /><Button aria-label="Square action" shape="square"><Icon decorative name="i-ph-plus" /></Button><Button disabled label="Disabled" /><Button label="Loading" loading />
    </div>
    <ButtonGroup attached label="View density"><Button label="Compact" size="sm" variant="outline" /><Button label="Comfortable" size="sm" variant="outline" /></ButtonGroup>
    <Typography as="h3" tone="heading" variant="h4">Badges, avatars, and icons</Typography>
    <div v-for="variant in badgeVariants" :key="variant" class="flex flex-wrap items-center gap-3"><Badge v-for="tone in uiTones" :key="tone" dot :label="`${tone} ${variant}`" :tone="tone" :variant="variant" /></div>
    <div class="flex flex-wrap items-end gap-3">
      <Avatar v-for="size in avatarSizes" :key="size" name="Ada Lovelace" :size="size" :status="size === 'lg' ? 'busy' : size === 'xl' ? 'offline' : 'online'" /><Icon v-for="size in loadingSizes" :key="size" label="Settings" name="i-ph-gear" :size="size" tone="primary" />
    </div>
    <Typography as="h3" tone="heading" variant="h4">Alert tones and surfaces</Typography>
    <div v-for="variant in alertVariants" :key="variant" class="grid gap-2 md:grid-cols-2">
      <Alert v-for="tone in uiTones" :key="tone" :dismissible="tone === 'info'" icon="i-ph-info" :title="`${tone} ${variant}`" :tone="tone" :variant="variant">Live-region, action, overflow, and dismiss contracts remain visible.</Alert>
    </div>
    <Divider label="Composition" />
    <div class="grid gap-3"><Breadcrumbs :items="breadcrumbs" density="compact" /><Breadcrumbs :items="breadcrumbs" /></div>
    <Typography as="h3" tone="heading" variant="h4">Card surfaces</Typography>
    <div class="grid gap-4 md:grid-cols-3">
      <Card v-for="variant in cardVariants" :key="variant" :interactive="variant === 'elevated'" :variant="variant"
        ><Typography as="h3" tone="heading" variant="h4">{{ variant }}</Typography
        ><Typography variant="small">Portable surface treatment.</Typography></Card
      >
    </div>
    <Typography as="h3" tone="heading" variant="h4">Typography and lists</Typography>
    <div class="grid gap-2">
      <Typography v-for="variant in typographySamples" :key="variant" :variant="variant">{{ variant }} specimen</Typography>
    </div>
    <div class="grid gap-4 md:grid-cols-2">
      <List v-for="variant in listVariants" :key="variant" :variant="variant"
        ><li>{{ variant }} item</li>
        <li>Overflow remains caller-controlled</li>
        <li>Spacing uses foundation tokens</li></List
      >
    </div>
    <Typography as="h3" tone="heading" variant="h4">Progress, loading, and skeleton states</Typography>
    <div class="grid gap-3"><Progress v-for="tone in uiTones" :key="tone" :label="`${tone} progress`" show-value :tone="tone" :value="72" /><Progress v-for="size in progressSizes" :key="size" :label="`${size} indeterminate`" :size="size" /></div>
    <div class="flex flex-wrap items-center gap-4"><Spinner v-for="size in loadingSizes" :key="size" :label="`${size} loading specimen`" :size="size" /></div>
    <div class="grid grid-cols-3 items-start gap-4">
      <Skeleton v-for="shape in skeletonShapes" :key="shape" :shape="shape" />
    </div>
    <Divider decorative />
    <div class="grid gap-4 md:grid-cols-2">
      <Stat v-for="tone in uiTones" :key="tone" delta="+12%" description="from last round" icon="i-ph-chart-line-up" :label="`${tone} stat`" :tone="tone" trend="up" :value="48" /><EmptyState
        description="Use the action slot to provide the next useful step."
        icon="i-ph-folder-open"
        title="Nothing here yet"
        ><template #actions><Button label="Create item" size="sm" /></template></EmptyState
      ><EmptyState compact description="Compact empty treatment." title="No compact results" />
    </div>
  </section>
</template>
