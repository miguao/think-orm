import { ThemeProvider } from "@lobehub/ui";
import "antd/dist/reset.css";
import {
  siteSelectors,
  useSiteStore,
  useThemeStore,
} from "dumi-theme-lobehub/dist/store";
import isEqual from "fast-deep-equal";
import { memo, useEffect } from "react";
// @ts-ignore
import _customToken from "dumi-theme-lobehub/dist/styles/customToken";
// @ts-ignore
import AntdStaticMethods from "dumi-theme-lobehub/dist/layouts/DocLayout/AntdStaticMethods";
// @ts-ignore
import AntdV5MonkeyPatch from "dumi-theme-lobehub/dist/layouts/DocLayout/AntdV5MonkeyPatch";
// @ts-ignore
import ConfigProvider from "dumi-theme-lobehub/dist/layouts/DocLayout/ConfigProvider";
// @ts-ignore
import StyleRegistry from "dumi-theme-lobehub/dist/layouts/DocLayout/StyleRegistry";

const CustomThemeProvider = memo(
  ({ children }: { children: React.ReactNode }) => {
    // 强制设置主题为深色
    useEffect(() => {
      useThemeStore.setState({ themeMode: "dark" });
      // 确保 HTML 属性也是深色
      document.documentElement.setAttribute("data-prefers-color", "dark");
      document.documentElement.style.colorScheme = "dark";
    }, []);

    const userToken = useSiteStore(siteSelectors.token, isEqual);

    return (
      <StyleRegistry>
        <ThemeProvider
          appearance="dark"
          customToken={(themeToken: any) =>
            Object.assign({}, _customToken(themeToken), userToken)
          }
          themeMode="dark"
        >
          <AntdStaticMethods />
          <ConfigProvider>{children}</ConfigProvider>
        </ThemeProvider>
        <AntdV5MonkeyPatch />
      </StyleRegistry>
    );
  }
);

CustomThemeProvider.displayName = "ThemeProvider";

export default CustomThemeProvider;
