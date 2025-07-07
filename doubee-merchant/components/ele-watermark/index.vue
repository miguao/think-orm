<!-- 水印 -->
<template>
  <div
    ref="rootRef"
    :key="rootKey"
    :style="!wrapPosition || fixed ? void 0 : { position: 'relative' }"
  >
    <slot></slot>
    <div v-if="!disabled" ref="imgRef" :style="imgStyle"></div>
  </div>
</template>

<script lang="ts" setup>
  import type { Ref } from 'vue';
  import { ref, onMounted, watch, nextTick } from 'vue';
  import type { StyleValue } from '../ele-app/types';
  import {
    getFont,
    getGap,
    getPixelRatio,
    rotateWatermark,
    joinStyle,
    useMutation
  } from './util';
  import { watermarkProps } from './props';

  defineOptions({ name: 'EleWatermark' });

  const props = defineProps(watermarkProps);

  const SIZE = 2;

  const { observe, disconnect } = useMutation({
    getRoot: () => rootRef.value,
    getEl: () => imgRef.value,
    onDeleted: () => {
      disconnect();
      rootKey.value = rootKey.value + 1;
      nextTick(() => {
        render();
      });
    },
    onDalsified: () => {
      render();
    }
  });

  /** 用于重新渲染根节点 */
  const rootKey = ref(1);

  /** 根节点 */
  const rootRef = ref<HTMLDivElement | null>(null);

  /** 水印节点 */
  const imgRef = ref<HTMLDivElement | null>(null);

  /** 水印样式 */
  const imgStyle: Ref<StyleValue> = ref<StyleValue>({ display: 'none' });

  /** 设置并冻结水印样式 */
  const updateStyle = (base64?: string, size?: string) => {
    const position = props.fixed ? 'fixed' : 'absolute';
    const width = props.fixed ? '100vw' : '100%';
    const height = props.fixed ? '100vh' : '100%';
    const style: StyleValue = {
      position: `${position} !important` as any,
      top: '0 !important',
      left: '0 !important',
      right: 'auto !important',
      bottom: 'auto !important',
      width: `${width} !important`,
      height: `${height} !important`,
      pointerEvents: 'none !important' as any,
      backgroundRepeat: 'repeat !important',
      opacity: '1 !important',
      transform: 'none !important',
      filter: 'none !important',
      visibility: 'visible !important' as any,
      transition: 'none !important',
      maxWidth: 'none !important',
      maxHeight: 'none !important',
      zoom: '1 !important',
      mask: 'none !important',
      clipPath: 'none !important',
      display: 'block !important',
      ...(props.customStyle || {})
    };
    if (!base64) {
      style.display = 'none !important';
    } else {
      style.backgroundImage = `url('${base64}') !important`;
      if (size) {
        style.backgroundSize = `${size} !important`;
      }
      style.zIndex = `${props.zIndex ?? 2147483647} !important` as any;
    }
    disconnect();
    if (imgRef.value && imgRef.value.style && imgRef.value.style.cssText) {
      imgRef.value.style.cssText = joinStyle(style);
      observe();
    } else {
      imgStyle.value = style;
      nextTick(() => {
        observe();
      });
    }
  };

  /** 获取默认宽度 */
  const getDefaultWidth = (ctx: CanvasRenderingContext2D) => {
    if (props.image || !ctx.measureText) {
      return 120;
    }
    const content = props.content;
    const contents = Array.isArray(content) ? content : [content];
    const widths = contents.map((item) => ctx.measureText(item!).width);
    return Math.ceil(Math.max(...widths));
  };

  /** 获取默认高度 */
  const getDefaultHeight = (ctx: CanvasRenderingContext2D) => {
    if (props.image || !ctx.measureText) {
      return 64;
    }
    const lines = Array.isArray(props.content) ? props.content.length : 1;
    const { fontSize } = getFont(props.font);
    return fontSize * lines + (lines - 1) * props.lineGap;
  };

  /** 获取水印尺寸 */
  const getMarkSize = (ctx: CanvasRenderingContext2D) => {
    const width = props.width;
    const height = props.height;
    return [width ?? getDefaultWidth(ctx), height ?? getDefaultHeight(ctx)];
  };

  /** 绘制文本 */
  const fillTexts = (
    ctx: CanvasRenderingContext2D,
    drawX: number,
    drawY: number,
    drawWidth: number,
    drawHeight: number
  ) => {
    const ratio = getPixelRatio();
    const { color, fontSize, fontStyle, fontWeight, fontFamily } = getFont(
      props.font
    );
    const mergedFontSize = fontSize * ratio;
    ctx.font = `${fontStyle} normal ${fontWeight} ${mergedFontSize}px/${drawHeight}px ${fontFamily}`;
    ctx.fillStyle = color;
    ctx.textAlign = 'center';
    ctx.textBaseline = 'top';
    ctx.translate(drawWidth / 2, 0);
    const content = props.content;
    const contents = Array.isArray(content) ? content : [content];
    contents.forEach((item, index) => {
      ctx.fillText(
        item ?? '',
        drawX,
        drawY + index * (mergedFontSize + props.lineGap * ratio)
      );
    });
  };

  /** 渲染 */
  const render = () => {
    if (props.disabled) {
      disconnect();
      imgStyle.value = { display: 'none' };
      return;
    }
    const canvas = document.createElement('canvas');
    const ctx = canvas.getContext('2d');
    if (!ctx) {
      return;
    }
    const rotate = props.rotate ?? -22;

    if (!props.image && ctx.measureText != null) {
      const { fontSize, fontFamily } = getFont(props.font);
      ctx.font = `${fontSize}px ${fontFamily}`;
    }

    const [markWidth, markHeight] = getMarkSize(ctx);
    const ratio = getPixelRatio();
    const [gapX, gapY] = getGap(props.gap);
    const canvasWidth = (gapX + markWidth) * ratio;
    const canvasHeight = (gapY + markHeight) * ratio;
    canvas.setAttribute('width', `${canvasWidth * SIZE}px`);
    canvas.setAttribute('height', `${canvasHeight * SIZE}px`);

    const drawWidth = markWidth * ratio;
    const drawHeight = markHeight * ratio;
    const rotateX = (drawWidth + gapX * ratio) / 2;
    const rotateY = (drawHeight + gapY * ratio) / 2;
    ctx.save();
    rotateWatermark(ctx, rotateX, rotateY, rotate);

    // 绘制内容
    const drawX = (gapX * ratio) / 2;
    const drawY = (gapY * ratio) / 2;
    const alternateDrawX = drawX + canvasWidth;
    const alternateDrawY = drawY + canvasHeight;
    const alternateRotateX = rotateX + canvasWidth;
    const alternateRotateY = rotateY + canvasHeight;
    if (props.image) {
      const img = new Image();
      img.onload = () => {
        ctx.drawImage(img, drawX, drawY, drawWidth, drawHeight);
        ctx.restore();
        rotateWatermark(ctx, alternateRotateX, alternateRotateY, rotate);
        ctx.drawImage(
          img,
          alternateDrawX,
          alternateDrawY,
          drawWidth,
          drawHeight
        );
        updateStyle(canvas.toDataURL(), `${(gapX + markWidth) * SIZE}px`);
      };
      img.crossOrigin = 'anonymous';
      img.referrerPolicy = 'no-referrer';
      img.src = props.image;
    } else {
      fillTexts(ctx, drawX, drawY, drawWidth, drawHeight);
      ctx.restore();
      rotateWatermark(ctx, alternateRotateX, alternateRotateY, rotate);
      fillTexts(ctx, alternateDrawX, alternateDrawY, drawWidth, drawHeight);
      updateStyle(canvas.toDataURL(), `${(gapX + markWidth) * SIZE}px`);
    }
  };

  onMounted(() => {
    render();
  });

  watch(
    () => props,
    () => {
      render();
    },
    { deep: true }
  );
</script>
