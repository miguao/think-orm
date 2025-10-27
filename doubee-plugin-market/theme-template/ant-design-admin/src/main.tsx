import { createRoot } from "react-dom/client";
import { BrowserRouter } from "react-router";
import "@ant-design/v5-patch-for-react-19";
import "@/styles/global.less";
import { App as AntdApp, ConfigProvider } from "antd";
import { ProConfigProvider } from "@ant-design/pro-components";
import { Provider } from "react-redux";
import store from "./modules/store";
import Layouts from "./layouts";

createRoot(document.getElementById("root")!).render(
  <Provider store={store}>
    <ProConfigProvider>
      <ConfigProvider
        theme={{
          token: {
            colorPrimary: "#1741d8",
            colorInfo: "#1741d8",
            borderRadius: 4,
          },
        }}
      >
        <AntdApp>
          <BrowserRouter>
            <Layouts />
          </BrowserRouter>
        </AntdApp>
      </ConfigProvider>
    </ProConfigProvider>
  </Provider>
);
