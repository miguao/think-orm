/** 包裹组件 */
import { defineComponent } from 'vue';

export default defineComponent({
  name: 'EleApp',
  setup(_props, { slots }) {
    return () => slots.default?.();
  }
});
