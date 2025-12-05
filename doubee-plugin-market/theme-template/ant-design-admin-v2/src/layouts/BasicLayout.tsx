import { Outlet, useNavigate, useLocation } from "react-router-dom";
import { ProLayout, PageContainer } from "@ant-design/pro-components";
import { HomeOutlined, SettingOutlined, UserOutlined } from "@ant-design/icons";
import { useState } from "react";

export default function BasicLayout() {
  const navigate = useNavigate();
  const location = useLocation();
  const [collapsed, setCollapsed] = useState(false);

  const route = {
    path: "/",
    routes: [
      {
        path: "/",
        name: "控制台",
        icon: <HomeOutlined />,
      },
      {
        path: "/system",
        name: "系统管理",
        icon: <SettingOutlined />,
        routes: [
          {
            path: "/system/user",
            name: "用户管理",
            icon: <UserOutlined />,
          },
        ],
      },
    ],
  };

  return (
    <ProLayout
      title="Ant Design Pro"
      layout="side"
      siderMenuType="group"
      collapsed={collapsed}
      onCollapse={setCollapsed}
      location={{ pathname: location.pathname }}
      route={route}
      menuItemRender={(item, dom) => (
        <div onClick={() => item.path && navigate(item.path)}>{dom}</div>
      )}
    >
      <PageContainer>
        <Outlet />
      </PageContainer>
    </ProLayout>
  );
}
