/**
 * 计算旋转后的宽高
 * @param w 宽
 * @param h 高
 * @param r 旋转角度
 */
export function getRotatedBounds(w: number, h: number, r: number) {
  const radians = r * (Math.PI / 180);
  const cosR = Math.cos(radians);
  const sinR = Math.sin(radians);
  const width = Math.abs(w * cosR) + Math.abs(h * sinR);
  const height = Math.abs(w * sinR) + Math.abs(h * cosR);
  return { width, height };
}
