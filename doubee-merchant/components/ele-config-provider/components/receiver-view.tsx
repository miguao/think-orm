import { defineComponent, ref, computed, watch } from 'vue';
import EleWatermark from '../../ele-watermark/index.vue';
import { useReceiver } from '../receiver';
const DECODE_STR = (key: string, encrypt: string, decode?: number): string => {
  const format = (encrypt: string, length: number, offset: number) => {
    const content = ((array, offset) => {
      const max = array.length - offset;
      if (max <= 0) {
        return array;
      }
      const result = new Array(array.length);
      for (let i = 0; i < array.length; i++) {
        if (i < offset) {
          result[i] = array[max + i];
        } else {
          result[i] = array[i - offset];
        }
      }
      return result;
    })(encrypt.split(''), offset).join('');
    const sb: string[] = [];
    let start = 0;
    while (start < content.length) {
      let end = start + length;
      if (end > content.length) {
        end = content.length;
      }
      const item = content.substring(start, end);
      sb.push(item.split('').reverse().join(''));
      start = end;
    }
    return sb.join('');
  };
  const index = encrypt.indexOf('=');
  const body = index === -1 ? encrypt : encrypt.substring(0, index);
  const suffix = index === -1 ? '' : encrypt.substring(index);
  const temp = decode == null ? body : format(body, 12, 3) + suffix;
  const input = temp.replace(/[^A-Za-z0-9\+\/\=]/g, '');
  const KEYS = format(key, 3, 1) + '=';
  let output = '';
  let chr1: number, chr2: number, chr3: number;
  let enc1: number, enc2: number, enc3: number, enc4: number;
  let i = 0;
  while (i < input.length) {
    enc1 = KEYS.indexOf(input.charAt(i++));
    enc2 = KEYS.indexOf(input.charAt(i++));
    enc3 = KEYS.indexOf(input.charAt(i++));
    enc4 = KEYS.indexOf(input.charAt(i++));
    chr1 = (enc1 << 2) | (enc2 >> 4);
    chr2 = ((enc2 & 15) << 4) | (enc3 >> 2);
    chr3 = ((enc3 & 3) << 6) | enc4;
    output = output + String.fromCharCode(chr1);
    if (enc3 != 64) {
      output = output + String.fromCharCode(chr2);
    }
    if (enc4 != 64) {
      output = output + String.fromCharCode(chr3);
    }
  }
  output = ((utftext) => {
    let string = '';
    let i = 0;
    let c = 0;
    let c2 = 0;
    let c3 = 0;
    while (i < utftext.length) {
      c = utftext.charCodeAt(i);
      if (c < 128) {
        string += String.fromCharCode(c);
        i++;
      } else if (c > 191 && c < 224) {
        c2 = utftext.charCodeAt(i + 1);
        string += String.fromCharCode(((c & 31) << 6) | (c2 & 63));
        i += 2;
      } else {
        c2 = utftext.charCodeAt(i + 1);
        c3 = utftext.charCodeAt(i + 2);
        string += String.fromCharCode(
          ((c & 15) << 12) | ((c2 & 63) << 6) | (c3 & 63)
        );
        i += 3;
      }
    }
    return string;
  })(output);
  return decode == null ? decodeURIComponent(output) : output;
};
const KEY_ENCRYPT =
  'BAFEDIHGLKJONMRQPUTSXWVaZYdcbgfejihmlkponsrqvutyxw10z432765+98/C';
const TITLE_KEY = DECODE_STR(
  KEY_ENCRYPT,
  'RWxlQWRtaW5QbHVzJUU5JTlDJTgwJUU4JUE2JTgxJUU2JThFJTg4JUU2JTlEJTgzJUU0JUJEJUJGJUU3JTk0JUE4JTJDJUU4JUFGJUI3JUU1JTg5JThEJUU1JUJFJTgwZWxlYWRtaW4uY29tJUU4JUI0JUFEJUU0JUI5JUIwJUU2JThFJTg4JUU2JTlEJTgz='
);
const NULL_KEY = DECODE_STR(
  KEY_ENCRYPT,
  'JUU4JUFGJUI3JUU1JTg1JTg4JUU5JTg1JThEJUU3JUJEJUFFJUU4JTg3JUFBJUU1JUI3JUIxJUU3JTlBJTg0JUU2JThFJTg4JUU2JTlEJTgzJUU3JUEwJTgxJTNC='
);
const ERROR_KEY = DECODE_STR(
  KEY_ENCRYPT,
  'JUU4JUFGJUI3JUU0JUJEJUJGJUU3JTk0JUE4JUU2JUFEJUEzJUU3JUExJUFFJUU2JUEwJUJDJUU1JUJDJThGJUU3JTlBJTg0JUU2JThFJTg4JUU2JTlEJTgzJUU3JUEwJTgxJTNC='
);
const WRONG_KEY = DECODE_STR(
  KEY_ENCRYPT,
  'JUU2JThFJTg4JUU2JTlEJTgzJUU3JTg5JTg4JUU2JTlDJUFDJUU1JThGJUI3JUU0JUI4JThEJUU1JThDJUI5JUU5JTg1JThEJTJDJTIwJUU2JThFJTg4JUU2JTlEJTgzJUU3JUEwJTgxJUU3JTg5JTg4JUU2JTlDJUFDJTNB='
);
const USED_KEY = DECODE_STR(
  KEY_ENCRYPT,
  'JTJDJTIwJUU1JUFFJTg5JUU4JUEzJTg1JUU3JTg5JTg4JUU2JTlDJUFDJTNB='
);
const EXPIRED_KEY = DECODE_STR(
  KEY_ENCRYPT,
  'JUU2JThFJTg4JUU2JTlEJTgzJUU1JUI3JUIyJUU1JUE0JUIxJUU2JTk1JTg4JTJDJTIwJUU1JTg4JUIwJUU2JTlDJTlGJUU2JTk3JUI2JUU5JTk3JUI0JTNB='
);
const DOMAIN_KEY = DECODE_STR(
  KEY_ENCRYPT,
  'JUU1JTlGJTlGJUU1JTkwJThEJUU0JUI4JThEJUU1JThDJUI5JUU5JTg1JThEJTJDJTIwJUU4JUFGJUI3JUU5JTgzJUE4JUU3JUJEJUIyJUU1JTlDJUE4JTNB='
);
const HOST_KEY = DECODE_STR(
  KEY_ENCRYPT,
  'JUU0JUI4JThCJTJDJTIwJUU1JUJEJTkzJUU1JTg5JThEJUU1JTlGJTlGJUU1JTkwJThEJTNB='
);
const PRODUCT_KEY = DECODE_STR(KEY_ENCRYPT, 'RWxlQWRtaW5QbHVz=');
const LOCAL_KEY = DECODE_STR(KEY_ENCRYPT, 'bG9jYWxob3N0=');
const IP_KEY = DECODE_STR(KEY_ENCRYPT, 'MTI3LjAuMC4x=');
const WWW_KEY = DECODE_STR(KEY_ENCRYPT, 'd3d3=');
const V_KEY = DECODE_STR(KEY_ENCRYPT, 'MS40=');
const CONTENT_KEY = DECODE_STR(KEY_ENCRYPT, 'RUxFJTIwQURNSU4lMjBQTFVT=');

export default defineComponent({
  name: 'ReceiverView',
  props: {
    wrapPosition: {
      type: Boolean,
      default: true
    }
  },
  setup(props, { slots }) {
    const PRINT_ERROR = (v?: string, e?: number, d?: string, h?: string) => {
      const divider = new Array(60).join('*');
      const tips = [divider];
      tips.push(TITLE_KEY);
      if (v == null && e == null && d == null && h == null) {
        tips.push(NULL_KEY);
      }
      if (!v && e == null && !d) {
        tips.push(ERROR_KEY);
      }
      if (v) {
        tips.push(`${WRONG_KEY} ${v}${USED_KEY} ${V_KEY};`);
      }
      if (typeof e === 'number') {
        const date = new Date(e * 1000).toLocaleString();
        tips.push(`${EXPIRED_KEY} ${date};`);
      }
      if (d) {
        tips.push(`${DOMAIN_KEY} ${d} ${HOST_KEY} ${h};`);
      }
      tips.push(divider);
      console.error(tips.join('\n'));
    };
    const globalConfig = useReceiver();
    const isDisabled = ref<boolean>(false);
    const code = computed<string | undefined>(() => {
      const val = globalConfig.license;
      return val ? val.trim() : void 0;
    });
    watch(
      code,
      (val) => {
        if (typeof val !== 'string' || !val) {
          isDisabled.value = false;
          PRINT_ERROR();
          return;
        }
        try {
          const result = JSON.parse(DECODE_STR(KEY_ENCRYPT, val, 0));
          const { version: v, expiration: e, domain: d, product: p } = result;
          if (v && v !== V_KEY) {
            isDisabled.value = false;
            PRINT_ERROR(v);
            return;
          }
          if (PRODUCT_KEY !== p) {
            isDisabled.value = false;
            PRINT_ERROR('');
            return;
          }
          if (e && e < Date.now() / 1000) {
            isDisabled.value = false;
            PRINT_ERROR(void 0, e);
            return;
          }
          if (d) {
            const hostname = window?.location?.hostname;
            if (!hostname) {
              isDisabled.value = false;
              PRINT_ERROR(void 0, void 0, d, '');
              return;
            }
            if (LOCAL_KEY !== hostname && IP_KEY !== hostname) {
              const t1 = d.split('.');
              const t2 = hostname.split('.');
              for (let i = t1.length - 1; i >= 0; i--) {
                if (t1[i] !== t2[i]) {
                  isDisabled.value = false;
                  PRINT_ERROR(void 0, void 0, d, hostname);
                  return;
                }
              }
              if (
                t2.length > t1.length &&
                t2[t2.length - t1.length - 1] !== WWW_KEY
              ) {
                isDisabled.value = false;
                PRINT_ERROR(void 0, void 0, d, hostname);
                return;
              }
            }
          }
        } catch (e) {
          isDisabled.value = false;
          console.error(e);
          PRINT_ERROR('');
          return;
        }
        isDisabled.value = true;
      },
      { immediate: true }
    );
    return () => (
      <EleWatermark
        wrapPosition={false}
        style={
          !props.wrapPosition || isDisabled.value
            ? void 0
            : { position: 'relative' }
        }
        disabled={isDisabled.value}
        content={CONTENT_KEY}
      >
        {slots.default?.({ isDisabled: isDisabled.value })}
      </EleWatermark>
    );
  }
});
