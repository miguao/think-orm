// src/pages/login/index.tsx
import { useState } from "react";
import { Form, Input, Button, Checkbox, message, Card } from "antd";
import { UserOutlined, LockOutlined } from "@ant-design/icons";
import { useNavigate } from "react-router-dom";

export default function LoginPage() {
  const [loading, setLoading] = useState(false);
  const navigate = useNavigate();

  const onFinish = (values: any) => {
    setLoading(true);

    // 模拟登录请求
    setTimeout(() => {
      setLoading(false);
      if (values.username === "admin" && values.password === "123456") {
        message.success("登录成功！");
        navigate("/"); // 登录成功跳转首页
      } else {
        message.error("用户名或密码错误！");
      }
    }, 1000);
  };

  return (
    <div className="flex-center h-screen bg-gray-100">
      <Card className="w-96 p-6 shadow-lg rounded-lg" title="后台管理登录">
        <Form
          name="login"
          initialValues={{ remember: true }}
          onFinish={onFinish}
          layout="vertical"
        >
          <Form.Item
            name="username"
            label="用户名"
            rules={[{ required: true, message: "请输入用户名" }]}
          >
            <Input
              prefix={<UserOutlined />}
              placeholder="请输入用户名"
              size="large"
            />
          </Form.Item>

          <Form.Item
            name="password"
            label="密码"
            rules={[{ required: true, message: "请输入密码" }]}
          >
            <Input.Password
              prefix={<LockOutlined />}
              placeholder="请输入密码"
              size="large"
            />
          </Form.Item>

          <Form.Item name="remember" valuePropName="checked">
            <Checkbox>记住我</Checkbox>
          </Form.Item>

          <Form.Item>
            <Button
              type="primary"
              htmlType="submit"
              block
              loading={loading}
              className="mt-4"
            >
              登录
            </Button>
          </Form.Item>
        </Form>
      </Card>
    </div>
  );
}
