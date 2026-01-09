import { ProCard } from "@ant-design/pro-components";
import { Empty, Space } from "antd";
import Layout from "@/layouts";

export default () => {
  return (
    <Layout>
      <Space direction="vertical" size="middle" style={{ display: "flex" }}>
        <ProCard>
          <Empty image="/images/empty.svg"></Empty>
        </ProCard>
      </Space>
    </Layout>
  );
};
