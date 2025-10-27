import { RootState } from "@/modules/store";
import tree from "@/utils/tree";
import { LogoutOutlined } from "@ant-design/icons";
import { ProLayout } from "@ant-design/pro-components";
import { Dropdown } from "antd";
import { memo } from "react";
import { useSelector } from "react-redux";
import { Outlet, useNavigate } from "react-router-dom";

export default memo(() => {
  const user = useSelector((state: RootState) => state.user);
  const routes = tree.generate(user.userInfo.menus);

  const navigate = useNavigate();
  
  return (
    <ProLayout
      title={import.meta.env.VITE_APP_TITLE}
      layout="mix"
      siderMenuType="group"
      route={{ routes }}
      bgLayoutImgList={[
        {
          src: "https://img.alicdn.com/imgextra/i2/O1CN01O4etvp1DvpFLKfuWq_!!6000000000279-2-tps-609-606.png",
          left: 85,
          bottom: 100,
          height: "303px",
        },
        {
          src: "https://img.alicdn.com/imgextra/i2/O1CN01O4etvp1DvpFLKfuWq_!!6000000000279-2-tps-609-606.png",
          bottom: -68,
          right: -45,
          height: "303px",
        },
        {
          src: "https://img.alicdn.com/imgextra/i3/O1CN018NxReL1shX85Yz6Cx_!!6000000005798-2-tps-884-496.png",
          bottom: 0,
          left: 0,
          width: "331px",
        },
      ]}
      token={{
        header: {
          colorBgMenuItemSelected: "rgba(0,0,0,0.04)",
          heightLayoutHeader: 50,
          colorBgHeader: "#F2F3F5",
        },
        sider: {
          colorBgCollapsedButton: "#fff",
          colorTextMenuSelected: "#1741D8",
          colorTextMenuItemHover: "#1741D8",
          colorBgMenuItemSelected: "#FFFFFF",
          colorBgMenuItemHover: "transparent",
          colorBgMenuItemActive: "transparent",
        },
        bgLayout: "#F2F3F5",
      }}
      menuItemRender={(item, dom) => {
        return (
          <div onClick={() => item.path && navigate(item.path!)}>{dom}</div>
        );
      }}
      avatarProps={{
        src: "/images/avatar.png",
        size: "small",
        title: user.userInfo?.nickname,
        render: (_, dom) => (
          <Dropdown
            menu={{
              items: [
                {
                  key: "logout",
                  icon: <LogoutOutlined />,
                  label: "退出登录",
                },
              ],
            }}
          >
            {dom}
          </Dropdown>
        ),
      }}
    >
      <Outlet />
    </ProLayout>
  );
});
